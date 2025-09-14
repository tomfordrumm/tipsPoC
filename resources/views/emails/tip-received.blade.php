<!doctype html>
<html>
<body>
    <h2>New tip received</h2>
    <p>Hi {{ $tip->user->name }},</p>
    <p>You have received a new tip.</p>
    <ul>
        <li>Amount: {{ strtoupper($tip->currency) }} {{ number_format($tip->amount_cents / 100, 2) }}</li>
        <li>Status: {{ $tip->status }}</li>
        <li>Paid at: {{ optional($tip->paid_at)->toDateTimeString() }}</li>
    </ul>
    <p>Thanks!</p>
</body>
<!-- This is a simple PoC email template. -->
</html>

