<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductImportController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getPathname(), 'r');

        if ($handle === false) {
            return redirect()->back()->with('error', 'Could not read the CSV file.');
        }

        $header = fgetcsv($handle, escape: '');

        if ($header && isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        }

        if (! $header || ! $this->validateHeader($header)) {
            fclose($handle);

            return redirect()->back()->with('error', 'Invalid CSV format. Expected: barcode,name,price,stock');
        }

        $imported = 0;
        $skipped = 0;

        while (($data = fgetcsv($handle, escape: '')) !== false) {
            if (count($data) < 4) {
                $skipped++;

                continue;
            }

            $barcode = trim($data[0]) ?: null;
            $name = trim($data[1]);
            $name = trim($name, '"\'');
            $price = floatval($data[2]);
            $stock = isset($data[3]) && trim($data[3]) !== '' ? intval($data[3]) : 0;

            if (empty($name) || $price < 0) {
                $skipped++;

                continue;
            }

            if ($barcode && $this->barcodeExists($barcode)) {
                $skipped++;

                continue;
            }

            if ($this->nameExists($name)) {
                $skipped++;

                continue;
            }

            Product::create([
                'barcode' => $barcode,
                'name' => $name,
                'description' => null,
                'price' => $price,
                'stock' => $stock,
                'stock_warning_level' => 0,
                'is_active' => true,
            ]);

            $imported++;
        }

        fclose($handle);

        $message = "Imported {$imported} products.";
        if ($skipped > 0) {
            $message .= " Skipped {$skipped} rows.";
        }

        return redirect()->route('products.index')->with('success', $message);
    }

    private function validateHeader(array $header): bool
    {
        return count($header) >= 4 &&
               strtolower($header[0]) === 'barcode' &&
               strtolower($header[1]) === 'name';
    }

    private function nameExists(string $name): bool
    {
        $normalizedValue = $this->normalizeName($name);

        return Product::whereRaw('LOWER(name) = ?', [$normalizedValue])
            ->exists();
    }

    private function barcodeExists(?string $barcode): bool
    {
        if (empty($barcode)) {
            return false;
        }

        return Product::whereRaw('LOWER(barcode) = ?', [strtolower($barcode)])
            ->whereNotNull('barcode')
            ->exists();
    }

    private function normalizeName(string $name): string
    {
        return preg_replace('/\s+/', ' ', strtolower(trim($name)));
    }
}
