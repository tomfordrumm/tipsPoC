<!doctype html>
<html>
<body>
    <h2>New payout request</h2>
    <ul>
        <li>User: {{ $payout->user->name }} (ID: {{ $payout->user_id }})</li>
        <li>Amount requested: {{ $payout->amount_cents ? ('EUR ' . number_format($payout->amount_cents / 100, 2)) : 'not specified' }}</li>
        <li>Status: {{ $payout->status }}</li>
        <li>Requested at: {{ optional($payout->requested_at)->toDateTimeString() }}</li>
    </ul>
</body>
</html>
