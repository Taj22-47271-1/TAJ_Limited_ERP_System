<?php
namespace App\Services;

use App\Models\Account;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    private function account(string $code): int
    {
        return Account::query()->where('code', $code)->value('id')
            ?? throw new \RuntimeException("Missing chart of account: {$code}");
    }

    private function line(JournalEntry $entry, string $code, float $debit, float $credit, string $description): void
    {
        $entry->lines()->create([
            'account_id' => $this->account($code),
            'debit' => round($debit, 2),
            'credit' => round($credit, 2),
            'description' => $description,
        ]);
    }

    public function postSale(Sale $sale): void
    {
        DB::transaction(function () use ($sale): void {
            $entry = JournalEntry::create([
                'entry_date'=>$sale->sale_date,
                'reference_type'=>'sale',
                'reference_id'=>$sale->id,
                'memo'=>'Sale '.$sale->invoice_no,
                'created_by'=>$sale->created_by,
            ]);

            $paid = (float) $sale->paid_amount;
            $due = (float) $sale->due_amount;
            if ($paid > 0) $this->line($entry, '1100', $paid, 0, 'Payment received');
            if ($due > 0) $this->line($entry, '1200', $due, 0, 'Customer receivable');
            $this->line($entry, '4100', 0, (float)$sale->total, 'Sales revenue');

            $cost = (float) $sale->items()->selectRaw('COALESCE(SUM(quantity * unit_cost), 0) AS total_cost')->value('total_cost');
            if ($cost > 0) {
                $this->line($entry, '5100', $cost, 0, 'Cost of goods sold');
                $this->line($entry, '1300', 0, $cost, 'Inventory reduction');
            }
        });
    }

    public function postReceivableCollection(Sale $sale, float $amount, ?int $userId = null): void
    {
        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($sale, $amount, $userId): void {
            $entry = JournalEntry::create([
                'entry_date'=>now()->toDateString(),
                'reference_type'=>'sale_collection',
                'reference_id'=>$sale->id,
                'memo'=>'Collection for '.$sale->invoice_no,
                'created_by'=>$userId,
            ]);
            $this->line($entry, '1100', $amount, 0, 'Customer payment received');
            $this->line($entry, '1200', 0, $amount, 'Accounts receivable settled');
        });
    }

    public function postPurchase(Purchase $purchase): void
    {
        DB::transaction(function () use ($purchase): void {
            $entry = JournalEntry::create([
                'entry_date'=>$purchase->purchase_date,
                'reference_type'=>'purchase',
                'reference_id'=>$purchase->id,
                'memo'=>'Purchase '.$purchase->invoice_no,
                'created_by'=>$purchase->created_by,
            ]);
            $this->line($entry, '1300', (float)$purchase->total, 0, 'Inventory purchase');
            if ((float)$purchase->paid_amount > 0) $this->line($entry, '1100', 0, (float)$purchase->paid_amount, 'Cash paid');
            if ((float)$purchase->due_amount > 0) $this->line($entry, '2100', 0, (float)$purchase->due_amount, 'Supplier payable');
        });
    }

    public function postExpense(Expense $expense): void
    {
        DB::transaction(function () use ($expense): void {
            $entry = JournalEntry::create([
                'entry_date'=>$expense->expense_date,
                'reference_type'=>'expense',
                'reference_id'=>$expense->id,
                'memo'=>$expense->description,
                'created_by'=>$expense->created_by,
            ]);
            $this->line($entry, '6100', (float)$expense->amount, 0, $expense->category);
            $this->line($entry, '1100', 0, (float)$expense->amount, 'Expense payment');
        });
    }
}
