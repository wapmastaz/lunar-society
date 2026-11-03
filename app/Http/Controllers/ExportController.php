<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * Paid founder names in slot order, for laying out the plaque.
     */
    public function engraving(): StreamedResponse
    {
        $rows = Order::query()->paid()->with('tier')->whereNotNull('slot_number')->orderBy('slot_number')->get()
            ->map(fn (Order $o) => [
                $o->slot_number,
                $o->tier->name,
                $o->plaque_name,
                $o->couple_addon ? 'yes' : 'no',
                $o->isLocked() ? 'locked' : 'editable until '.$o->locks_at?->toDateTimeString(),
                $o->email,
            ]);

        return $this->csv('engraving', ['Slot', 'Tier', 'Plaque name', 'Couple/family', 'Name status', 'Email'], $rows->all());
    }

    /**
     * Paid orders with a shipping address from PayPal.
     */
    public function shipping(): StreamedResponse
    {
        $rows = Order::query()->paid()->with('tier', 'product')->whereNotNull('shipping')->orderBy('paid_at')->get()
            ->map(fn (Order $o) => [
                $o->uuid,
                $o->paid_at?->toDateTimeString(),
                $o->itemLabel(),
                $o->slotLabel(),
                $o->plaque_name,
                Arr::get($o->shipping, 'name.full_name'),
                Arr::get($o->shipping, 'address.address_line_1'),
                Arr::get($o->shipping, 'address.address_line_2'),
                Arr::get($o->shipping, 'address.admin_area_2'),
                Arr::get($o->shipping, 'address.admin_area_1'),
                Arr::get($o->shipping, 'address.postal_code'),
                Arr::get($o->shipping, 'address.country_code'),
                $o->email,
            ]);

        return $this->csv('shipping', ['Order', 'Paid at', 'Item', 'Slot', 'Plaque name', 'Ship to', 'Address 1', 'Address 2', 'City', 'State', 'Postal code', 'Country', 'Email'], $rows->all());
    }

    /**
     * @param  list<string>  $header
     * @param  array<int, array<int, mixed>>  $rows
     */
    protected function csv(string $name, array $header, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($header, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $header, escape: '');
            foreach ($rows as $row) {
                fputcsv($out, array_map(fn ($v) => $this->safeCell($v), $row), escape: '');
            }
            fclose($out);
        }, "lunar-society-{$name}-".now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Stop spreadsheet apps from treating buyer input as a formula.
     */
    protected function safeCell(mixed $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
