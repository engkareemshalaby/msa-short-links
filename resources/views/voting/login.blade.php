<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#072841">
    <title>Vote sign in · MSA University</title>
    <link rel="icon" href="{{ asset('images/msa-logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/voting.css') }}">
</head>
<body class="login-page">
<main class="login-shell">
    <section class="login-story" aria-label="MSA voting">
        <div class="login-story-top"><img src="{{ asset('images/msa-logo-wide.png') }}" alt="MSA University"><span>MSA UNIVERSITY</span></div>
        <div class="login-story-copy"><span class="login-kicker">MEMBER VOTING</span><h1>Your voice.<br><em>Your choice.</em></h1><p>Help select eight members from the MSA community. Review your choices carefully before you submit your vote.</p></div>
        <div class="login-story-bottom"><span class="story-line"></span><span>36 members · 8 selections · 1 vote</span></div>
    </section>
    <section class="login-panel-vote">
        <div class="login-panel-top"><img src="{{ asset('images/msa-logo-wide.png') }}" alt="MSA University"><span>SECURE MEMBER ACCESS</span></div>
        <div class="login-form-wrap"><span class="login-kicker">WELCOME BACK</span><h2>Sign in to vote</h2><p>Enter your registered email address and Staff ID.</p>
            @if($errors->any())<div class="notice error" role="alert">The email address or Staff ID is incorrect.</div>@endif
            <form method="POST" action="{{ route('voting.login.store') }}">@csrf
                <label for="email">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="name@example.com" required autofocus>
                <label for="staff_id">Staff ID</label>
                <input id="staff_id" type="text" name="staff_id" inputmode="numeric" autocomplete="off" placeholder="Enter your Staff ID" required>
                <button type="submit">Sign in <span aria-hidden="true">→</span></button>
            </form>
            <div class="login-help"><span class="help-icon" aria-hidden="true">i</span><span>Your vote is final once submitted. You can review your selections first.</span></div>
        </div>
        <div class="login-panel-footer">MSA University <span>·</span> Member voting</div>
    </section>
</main>
</body>
</html>
