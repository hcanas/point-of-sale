<?php

namespace App\Http\Controllers;

use App\Enums\MemberLedgerType;
use App\Models\Member;
use App\Models\MemberLedger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MemberImportController extends Controller
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

            throw ValidationException::withMessages([
                'csv_file' => 'Invalid CSV header. Expected: last_name, first_name, middle_name, name_extension, address, tin_number, balance, share_capital',
            ]);
        }

        $imported = 0;
        $skipped = 0;

        while (($data = fgetcsv($handle, escape: '')) !== false) {
            if (count($data) < 6) {
                $skipped++;

                continue;
            }

            $lastName = trim($data[0]);
            $firstName = trim($data[1]);
            $middleName = trim($data[2]) ?: null;
            $nameExtension = trim($data[3]) ?: null;
            $address = trim($data[4]) ?: null;
            $tinNumber = trim($data[5]) ?: null;
            $balance = isset($data[6]) && trim($data[6]) !== '' ? floatval($data[6]) : 0;
            $shareCapital = isset($data[7]) && trim($data[7]) !== '' ? floatval($data[7]) : 0;

            if (empty($lastName) || empty($firstName)) {
                $skipped++;

                continue;
            }

            $nameExists = Member::query()
                ->whereRaw('LOWER(first_name) = LOWER(?)', [$firstName])
                ->whereRaw('LOWER(last_name) = LOWER(?)', [$lastName])
                ->when($middleName, fn ($q) => $q->whereRaw('LOWER(middle_name) = LOWER(?)', [$middleName]))
                ->when(! $middleName, fn ($q) => $q->whereNull('middle_name'))
                ->when($nameExtension, fn ($q) => $q->whereRaw('LOWER(name_extension) = LOWER(?)', [$nameExtension]))
                ->when(! $nameExtension, fn ($q) => $q->whereNull('name_extension'))
                ->exists();

            if ($nameExists) {
                $skipped++;

                continue;
            }

            $member = Member::create([
                'last_name' => $lastName,
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'name_extension' => $nameExtension,
                'address' => $address,
                'tin_number' => $tinNumber,
                'balance' => $balance,
                'share_capital' => $shareCapital,
                'is_active' => true,
            ]);

            if ($balance != 0) {
                MemberLedger::create([
                    'member_id' => $member->id,
                    'amount' => $balance,
                    'balance_after' => $balance,
                    'reference_type' => MemberLedgerType::Initial,
                    'reference_id' => null,
                    'notes' => 'Initial balance from CSV import',
                ]);
            }

            $imported++;
        }

        fclose($handle);

        $message = "Imported {$imported} members.";
        if ($skipped > 0) {
            $message .= " Skipped {$skipped} rows.";
        }

        return redirect()->route('members.index')->with('success', $message);
    }

    private function validateHeader(array $header): bool
    {
        if (count($header) < 8) {
            return false;
        }

        $normalized = array_map('strtolower', array_map('trim', $header));

        $expected = ['last_name', 'first_name', 'middle_name', 'name_extension', 'address', 'tin_number', 'balance', 'share_capital'];

        for ($i = 0; $i < count($expected); $i++) {
            if (! isset($normalized[$i]) || $normalized[$i] !== $expected[$i]) {
                return false;
            }
        }

        return true;
    }

    private function phoneExists(string $phone): bool
    {
        return Member::whereRaw('LOWER(phone) = LOWER(?)', [$phone])
            ->exists();
    }
}
