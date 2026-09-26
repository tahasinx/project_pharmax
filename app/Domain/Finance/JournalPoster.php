<?php

namespace App\Domain\Finance;

use App\Domain\Audit\AuditRecorder;
use App\Models\JournalEntry;
use App\Models\LedgerAccount;
use Illuminate\Support\Facades\DB;

class JournalPoster
{
    /**
     * @param  array<int, array{code: string, debit?: float, credit?: float}>  $lines
     */
    public function post(?int $branchId, string $date, string $sourceType, ?int $sourceId, string $memo, array $lines): JournalEntry
    {
        return DB::transaction(function () use ($branchId, $date, $sourceType, $sourceId, $memo, $lines) {
            $entry = JournalEntry::create([
                'branch_id' => $branchId,
                'entry_date' => $date,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'memo' => $memo,
                'user_id' => auth()->id(),
            ]);

            foreach ($lines as $line) {
                $account = LedgerAccount::where('code', $line['code'])->first();
                if (!$account) {
                    continue;
                }
                $debit = round((float) ($line['debit'] ?? 0), 2);
                $credit = round((float) ($line['credit'] ?? 0), 2);
                if ($debit == 0.0 && $credit == 0.0) {
                    continue;
                }
                $entry->lines()->create([
                    'ledger_account_id' => $account->id,
                    'branch_id' => $branchId,
                    'debit' => $debit,
                    'credit' => $credit,
                ]);
            }

            AuditRecorder::record('finance', $entry, 'posted', null, [
                'memo' => $memo,
                'source' => $sourceType,
                'lines' => $lines,
            ]);

            return $entry;
        });
    }
}
