@extends('layouts.admin')

@section('title', 'Payment Details - ' . ($payment['payment_number'] ?? ''))
@section('header-title', 'Payment Receipt')

@section('content')
<div class="ldg-show-container" style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <!-- Back Button -->
    <div style="margin-bottom: 20px;">
        <a href="javascript:history.back()" style="display: inline-flex; align-items: center; text-decoration: none; color: var(--text-2); font-size: 13px; font-weight: 500; gap: 6px;">
            ← Back
        </a>
    </div>

    <!-- Receipt Card -->
    <div class="ldg-receipt-card" style="background: var(--surface); border: 1px solid var(--border); border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 30px; position: relative;">
        <!-- Watermark / Status Badge -->
        <div style="position: absolute; top: 30px; right: 30px;">
            <span style="background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; padding: 6px 12px; border-radius: 50px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                Completed
            </span>
        </div>

        <!-- Header Info -->
        <div style="border-bottom: 1px dashed var(--border); padding-bottom: 20px; margin-bottom: 25px;">
            <div style="font-size: 11px; font-weight: 600; color: var(--text-3); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
                {{ $payment['payment_subtype'] === 'debit_refund' ? 'Debit Refund Voucher' : 'Payment Receipt' }}
            </div>
            <h2 style="font-size: 22px; font-weight: 700; color: var(--text); margin: 0;">
                {{ $payment['payment_number'] && $payment['payment_number'] !== '—' ? $payment['payment_number'] : 'Invoice-Time Payment' }}
            </h2>
            <div style="font-size: 12px; color: var(--text-2); margin-top: 6px;">
                Date: <strong>{{ $payment['date'] }}</strong>
            </div>
        </div>

        <!-- Payment Info Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div>
                <span style="display: block; font-size: 10px; font-weight: 600; color: var(--text-3); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Party Name</span>
                <span style="font-size: 13px; font-weight: 600; color: var(--text);">{{ $payment['party_name'] }}</span>
                <span style="display: block; font-size: 11px; color: var(--text-2); margin-top: 2px;">{{ $payment['party_type'] }}</span>
            </div>
            <div>
                <span style="display: block; font-size: 10px; font-weight: 600; color: var(--text-3); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Phone Number</span>
                <span style="font-size: 13px; font-weight: 600; color: var(--text);">{{ $payment['party_phone'] }}</span>
            </div>
            <div>
                <span style="display: block; font-size: 10px; font-weight: 600; color: var(--text-3); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Payment Method</span>
                <span style="font-size: 13px; font-weight: 600; color: var(--text);">{{ $payment['payment_method'] }}</span>
            </div>
            <div>
                <span style="display: block; font-size: 10px; font-weight: 600; color: var(--text-3); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Reference No</span>
                <span style="font-size: 13px; font-weight: 600; color: var(--text);">{{ $payment['reference_no'] }}</span>
            </div>
        </div>

        @if(!empty($payment['notes']) && $payment['notes'] !== '—')
        <div style="background: var(--bg); border-radius: 6px; padding: 15px; margin-bottom: 30px; border-left: 3px solid var(--accent);">
            <span style="display: block; font-size: 10px; font-weight: 600; color: var(--text-2); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Notes</span>
            <div style="font-size: 12px; color: var(--text); line-height: 1.5; white-space: pre-line;">{!! nl2br(e($payment['notes'])) !!}</div>
        </div>
        @endif

        <!-- Allocation Table -->
        <div>
            <h3 style="font-size: 13px; font-weight: 700; color: var(--text); margin-bottom: 12px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                Allocation Breakdown
            </h3>
            
            @if(empty($payment['allocations']))
                <div style="text-align: center; padding: 20px; color: var(--text-2); font-size: 12px; background: var(--bg); border-radius: 6px;">
                    No allocation details available
                </div>
            @else
                <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border); color: var(--text-2);">
                            <th style="padding: 10px 8px; font-weight: 600;">Type</th>
                            <th style="padding: 10px 8px; font-weight: 600;">Details</th>
                            <th style="padding: 10px 8px; font-weight: 600; text-align: right;">Allocated Amount</th>
                            <th style="padding: 10px 8px; font-weight: 600; text-align: right;">New Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payment['allocations'] as $alloc)
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 12px 8px; vertical-align: middle;">
                                    @if(($alloc['type'] ?? '') === 'opening_balance')
                                        <span style="background: #fef3c7; color: #d97706; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 600;">Opening Balance</span>
                                    @elseif(($alloc['type'] ?? '') === 'invoice')
                                        <span style="background: #dbeafe; color: #2563eb; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 600;">Invoice</span>
                                    @elseif(($alloc['type'] ?? '') === 'credit_note')
                                        <span style="background: #d1fae5; color: #059669; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 600;">Credit Note</span>
                                    @elseif(($alloc['type'] ?? '') === 'debit_note')
                                        <span style="background: #f5f3ff; color: #7c3aed; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 600;">Debit Note</span>
                                    @else
                                        <span style="background: #e5e7eb; color: #4b5563; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 600;">{{ ucfirst($alloc['type'] ?? 'Unknown') }}</span>
                                    @endif
                                </td>
                                <td style="padding: 12px 8px; vertical-align: middle;">
                                    @if(($alloc['type'] ?? '') === 'invoice')
                                        <strong>{{ $alloc['invoice_number'] ?? '—' }}</strong>
                                        @if(!empty($alloc['invoice_date']))
                                            <div style="font-size: 10px; color: var(--text-2); margin-top: 2px;">Date: {{ $alloc['invoice_date'] }}</div>
                                        @endif
                                        @if(!empty($alloc['grand_total']))
                                            <div style="font-size: 10px; color: var(--text-2);">Grand Total: ₹{{ number_format($alloc['grand_total'], 2) }}</div>
                                        @endif
                                    @elseif(($alloc['type'] ?? '') === 'credit_note')
                                        <strong>{{ $alloc['credit_note_number'] ?? '—' }}</strong>
                                        @if(!empty($alloc['description']))
                                            <div style="font-size: 10px; color: var(--text-2); margin-top: 2px;">{{ $alloc['description'] }}</div>
                                        @endif
                                    @elseif(($alloc['type'] ?? '') === 'debit_note')
                                        <strong>{{ $alloc['debit_note_number'] ?? '—' }}</strong>
                                        @if(!empty($alloc['debit_date']))
                                            <div style="font-size: 10px; color: var(--text-2); margin-top: 2px;">Date: {{ $alloc['debit_date'] }}</div>
                                        @endif
                                        @if(!empty($alloc['total_amount']))
                                            <div style="font-size: 10px; color: var(--text-2);">Total: ₹{{ number_format($alloc['total_amount'], 2) }}</div>
                                        @endif
                                    @else
                                        <span>{{ $alloc['description'] ?? '—' }}</span>
                                    @endif
                                </td>
                                <td style="padding: 12px 8px; vertical-align: middle; text-align: right; font-weight: 600; color: var(--text);">
                                    ₹{{ number_format($alloc['amount'] ?? 0, 2) }}
                                </td>
                                <td style="padding: 12px 8px; vertical-align: middle; text-align: right; color: var(--text-2);">
                                    @if(isset($alloc['new_balance']))
                                        ₹{{ number_format($alloc['new_balance'], 2) }}
                                    @elseif(isset($alloc['new_remaining']))
                                        ₹{{ number_format($alloc['new_remaining'], 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <!-- Receipt Footer / Summary -->
        <div style="display: flex; justify-content: flex-end; margin-top: 35px; border-top: 1px solid var(--border); padding-top: 20px;">
            <div style="text-align: right; width: 300px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;">
                    <span style="color: var(--text-2);">Total Paid Amount:</span>
                    <strong style="color: var(--text);">₹{{ number_format($payment['amount'] ?? 0, 2) }}</strong>
                </div>
                @if(($payment['discount'] ?? 0) > 0)
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;">
                        <span style="color: var(--text-2);">Discount Allowed:</span>
                        <strong style="color: #10b981;">₹{{ number_format($payment['discount'] ?? 0, 2) }}</strong>
                    </div>
                @endif
                <div style="display: flex; justify-content: space-between; border-top: 2px solid var(--text); padding-top: 10px; margin-top: 10px; font-size: 16px;">
                    <span style="font-weight: 700; color: var(--text);">Total Settled:</span>
                    <strong style="color: var(--text);">₹{{ number_format(($payment['amount'] ?? 0) + ($payment['discount'] ?? 0), 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
