<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kích hoạt tài khoản của bạn</title>
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
        .btn {
            background-color: #337ab7;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
        }
    </style>
</head>
<body>
<h1>Xin chào {{ $username }},</h1>

<p>Chào mừng bạn đến với {{ config('app.name') }}!</p>

<p>Vui lòng kích hoạt tài khoản của bạn bằng cách nhấp vào liên kết sau:</p>

<p><a href="{{ $link }}" class="btn">Kích hoạt tài khoản</a></p>

<p>Nếu bạn gặp bất kỳ khó khăn nào khi kích hoạt tài khoản, vui lòng liên hệ với chúng tôi.</p>

<p>Trân trọng,<br>Đội ngũ {{ config('app.name') }}</p>
</body>
</html>

