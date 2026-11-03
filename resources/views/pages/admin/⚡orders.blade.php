<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Tier;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Orders')] class extends Component {
    use WithPagination;

    #[Url(except: 'paid')]
    public string $status = 'paid';

    #[Url(except: '')]
    public string $tier = '';

    #[Url(except: '')]
    public string $search = '';

    public ?int $editingId = null;
    public string $editName = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['status', 'tier', 'search'])) {
            $this->resetPage();
        }
    }

    #[Computed]
    public function tiers()
    {
        return Tier::orderBy('slot_start')->get();
    }

    #[Computed]
    public function stats(): array
    {
        $paid = Order::query()->paid();

        return [
            'founders' => (clone $paid)->whereNotNull('tier_id')->count(),
            'byTier' => (clone $paid)->whereNotNull('tier_id')->selectRaw('tier_id, count(*) as c')->groupBy('tier_id')->pluck('c', 'tier_id'),
            'souvenirs' => (clone $paid)->whereNotNull('product_id')->count(),
            'gross' => (clone $paid)->sum('amount_cents'),
            'pending' => Order::where('status', OrderStatus::Pending)->where('hold_expires_at', '>', now())->count(),
        ];
    }

    #[Computed]
    public function orders()
    {
        return Order::query()
            ->with('tier', 'product')
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->when($this->tier === 'souvenir', fn ($q) => $q->whereNotNull('product_id'))
            ->when($this->tier !== '' && $this->tier !== 'souvenir', fn ($q) => $q->whereRelation('tier', 'code', $this->tier))
            ->when($this->search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('plaque_name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->when(is_numeric(ltrim($this->search, '#')), fn ($q) => $q->orWhere('slot_number', (int) ltrim($this->search, '#')))))
            ->orderByRaw('slot_number is null, slot_number')
            ->latest('paid_at')
            ->paginate(50);
    }

    public function edit(int $id): void
    {
        $order = Order::findOrFail($id);
        $this->editingId = $order->id;
        $this->editName = (string) $order->plaque_name;
        Flux::modal('edit-name')->show();
    }

    public function saveName(): void
    {
        $this->validate([
            'editName' => ['required', 'string', 'min:2', 'max:'.config('lunar.name_max_length'), "regex:/^[A-Za-z][A-Za-z .'&\-]*$/"],
        ]);

        Order::findOrFail($this->editingId)->update(['plaque_name' => trim($this->editName)]);

        Flux::modal('edit-name')->close();
        Flux::toast(variant: 'success', text: 'Name updated.');
        $this->editingId = null;
    }

    public function markRefunded(int $id): void
    {
        Order::findOrFail($id)->update([
            'status' => OrderStatus::Refunded,
            'refunded_at' => now(),
            'slot_number' => null,
        ]);

        unset($this->stats);
        Flux::toast(text: 'Marked as refunded. Remember to issue the refund in PayPal too.');
    }
}; ?>

<div class="space-y-6">
    <flux:heading size="xl" level="1">Orders</flux:heading>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <flux:card class="space-y-1">
            <flux:text>Founder slots sold</flux:text>
            <flux:heading size="xl">{{ number_format($this->stats['founders']) }} <span class="text-sm font-normal text-zinc-500">/ {{ number_format(config('lunar.capacity')) }}</span></flux:heading>
        </flux:card>
        @foreach ($this->tiers as $t)
            <flux:card class="space-y-1">
                <flux:text>{{ $t->name }}</flux:text>
                <flux:heading size="xl">{{ number_format($this->stats['byTier'][$t->id] ?? 0) }} <span class="text-sm font-normal text-zinc-500">/ {{ number_format($t->capacity()) }}</span></flux:heading>
            </flux:card>
        @endforeach
        <flux:card class="space-y-1">
            <flux:text>Souvenirs sold</flux:text>
            <flux:heading size="xl">{{ number_format($this->stats['souvenirs']) }}</flux:heading>
        </flux:card>
        <flux:card class="space-y-1">
            <flux:text>Gross (paid)</flux:text>
            <flux:heading size="xl">${{ number_format($this->stats['gross'] / 100, 2) }}</flux:heading>
            <flux:text class="text-xs">{{ $this->stats['pending'] }} checkout(s) in progress</flux:text>
        </flux:card>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Name, email or slot #" class="sm:max-w-xs" clearable />
        <flux:select wire:model.live="status" class="sm:max-w-40">
            <flux:select.option value="paid">Paid</flux:select.option>
            <flux:select.option value="pending">Pending</flux:select.option>
            <flux:select.option value="refunded">Refunded</flux:select.option>
            <flux:select.option value="expired">Expired</flux:select.option>
            <flux:select.option value="all">All</flux:select.option>
        </flux:select>
        <flux:select wire:model.live="tier" class="sm:max-w-48">
            <flux:select.option value="">All items</flux:select.option>
            @foreach ($this->tiers as $t)
                <flux:select.option :value="$t->code">{{ $t->name }}</flux:select.option>
            @endforeach
            <flux:select.option value="souvenir">Souvenirs</flux:select.option>
        </flux:select>
        <flux:spacer />
        <flux:button :href="route('admin.exports.engraving')" icon="arrow-down-tray" size="sm">Engraving CSV</flux:button>
        <flux:button :href="route('admin.exports.shipping')" icon="truck" size="sm">Shipping CSV</flux:button>
    </div>

    <flux:table :paginate="$this->orders">
        <flux:table.columns>
            <flux:table.column>Slot</flux:table.column>
            <flux:table.column>Plaque name</flux:table.column>
            <flux:table.column>Item</flux:table.column>
            <flux:table.column>Email</flux:table.column>
            <flux:table.column>Amount</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Paid</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($this->orders as $order)
                <flux:table.row :key="$order->id">
                    <flux:table.cell class="font-mono tabular-nums">{{ $order->slotLabel() ?? '·' }}</flux:table.cell>
                    <flux:table.cell>
                        {{ $order->plaque_name ?? '·' }}
                        @if ($order->couple_addon)
                            <flux:badge size="sm" color="amber" class="ms-1">couple</flux:badge>
                        @endif
                        @if ($order->isPaid() && $order->isFounder())
                            <flux:badge size="sm" :color="$order->isLocked() ? 'zinc' : 'green'" class="ms-1">{{ $order->isLocked() ? 'locked' : 'editable' }}</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>{{ $order->itemLabel() }}</flux:table.cell>
                    <flux:table.cell>{{ $order->email }}</flux:table.cell>
                    <flux:table.cell>{{ $order->amountLabel() }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="match ($order->status) { OrderStatus::Paid => 'green', OrderStatus::Pending => 'blue', OrderStatus::Refunded => 'red', default => 'zinc' }">{{ $order->status->value }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ $order->paid_at?->format('M j, g:i A') ?? '·' }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($order->isPaid())
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" aria-label="Order actions" />
                                <flux:menu>
                                    @if ($order->isFounder())
                                        <flux:menu.item icon="pencil-square" wire:click="edit({{ $order->id }})">Edit name</flux:menu.item>
                                    @endif
                                    <flux:menu.item icon="arrow-uturn-left" variant="danger" wire:click="markRefunded({{ $order->id }})" wire:confirm="Mark this order refunded and release its slot? Issue the refund in PayPal as well.">Mark refunded</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="text-center">No orders match these filters.</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal name="edit-name" class="md:w-96">
        <form wire:submit="saveName" class="space-y-6">
            <flux:heading size="lg">Edit plaque name</flux:heading>
            @if ($editingId && \App\Models\Order::find($editingId)?->isLocked())
                <flux:callout variant="warning" icon="exclamation-triangle" heading="This name is past its lock date. Only edit it if the engraving file hasn't been sent." />
            @endif
            <flux:input wire:model="editName" label="Name" maxlength="{{ config('lunar.name_max_length') }}" />
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Save</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
