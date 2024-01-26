<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lấy lại mật khẩu của bạn</title>
    <style>
        /* Kiểu dáng cơ bản cho giao diện chuyên nghiệp và thân thiện */
        body {
            font-family: sans-serif;
            margin: 40px;
        }
        a {
            color: #337ab7; /* Màu xanh chủ đạo */
            text-decoration: none;
        }
        .code {
            font-weight: bold;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
<h1>Xin chào {{ $username }},</h1>

<p>Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản của mình trên {{ config('app.name') }}.</p>

<p>Để hoàn tất quá trình này, vui lòng nhập mã OTP sau vào trang đặt lại mật khẩu:</p>

<p><span class="code">{{ $otp }}</span></p>

<p>Mã OTP này sẽ hết hạn sau {{ $otp_expiry }} phút.</p>

<p>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.</p>

<p>Trân trọng,<br>Đội ngũ {{ config('app.name') }}</p>
</body>
</html>
