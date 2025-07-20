<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to {{ $planName }}</title>
</head>
<body>
    <h1>Welcome to {{ $planName }}!</h1>
    
    <p>Thank you for subscribing to our {{ $planName }} plan.</p>
    
    <h2>Your Plan Features:</h2>
    <ul>
        @foreach($features as $feature)
            <li>{{ ucfirst(str_replace('_', ' ', $feature)) }}</li>
        @endforeach
    </ul>
    
    @if($trialEndsAt)
        <p>Your trial period ends on: {{ $trialEndsAt->format('F j, Y') }}</p>
    @endif
    
    <p>Your subscription will renew on: {{ $endsAt->format('F j, Y') }}</p>
    
    <p>If you have any questions, please don't hesitate to contact us.</p>
    
    <p>Best regards,<br>The Team</p>
</body>
</html>