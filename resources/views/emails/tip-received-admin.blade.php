<!doctype html>
<html>
<body>
    <h2>New tip (admin)</h2>
    <ul>
        <li>Waiter: {{ $tip->user->name }} (ID: {{ $tip->user_id }})</li>
        <li>Amount: {{ strtoupper($tip->currency) }} {{ number_format($tip->amount_cents / 100, 2) }}</li>
        <li>Payment Intent: {{ $tip->payment_intent_id ?? 'n/a' }}</li>
        <li>Checkout Session: {{ $tip->checkout_session_id ?? 'n/a' }}</li>
        <li>Status: {{ $tip->status }}</li>
        <li>Paid at: {{ optional($tip->paid_at)->toDateTimeString() }}</li>
    </ul>
</body>
<!-- Simple PoC admin notification. -->
</html>

