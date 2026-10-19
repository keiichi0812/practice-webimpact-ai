<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ライセンス {{ $license->id }}</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 24px; color: #222; }
        table { border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 6px 10px; text-align: left; }
        th { background: #eee; }
        .box { border: 1px solid #999; padding: 12px; margin: 12px 0; max-width: 640px; }
        .ok { color: #060; }
        .ng { color: #a00; }
        form.inline { display: inline; }
    </style>
</head>
<body>
    <p><a href="{{ route('licenses.index') }}">← 一覧へ</a></p>
    <h1>ライセンス #{{ $license->id }} {{ $license->customer_name }}</h1>

    @if (session('status'))<p class="ok">{{ session('status') }}</p>@endif
    @if ($errors->any())<ul class="ng">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>@endif

    <table>
        <tr><th>製品コード</th><td>{{ $license->product_code }}</td></tr>
        <tr><th>座席数</th><td>{{ $license->seats }}</td></tr>
        <tr><th>使用中</th><td>{{ $license->seats_used }}</td></tr>
        <tr><th>空き座席</th><td>{{ $license->remainingSeats() }}</td></tr>
        <tr><th>開始日</th><td>{{ $license->starts_on->format('Y-m-d') }}</td></tr>
        <tr><th>有効期限</th><td>{{ $license->expires_on->format('Y-m-d') }} {{ $license->isExpired() ? '(期限切れ)' : '' }}</td></tr>
        <tr><th>状態</th><td>{{ $license->status }}</td></tr>
    </table>

    <div class="box">
        <h2>有効期限を延長する</h2>
        <form method="post" action="{{ route('licenses.renew', $license) }}">
            @csrf
            <label><input type="number" name="months" min="1" max="60" value="12" style="width:5em"> か月延長</label>
            <button type="submit">延長</button>
        </form>
    </div>

    <div class="box">
        <h2>座席を割り当てる</h2>
        <form method="post" action="{{ route('licenses.assign', $license) }}">
            @csrf
            <label>メールアドレス <input type="email" name="user_email" required placeholder="user@example.com"></label>
            <button type="submit">割り当て</button>
        </form>
    </div>

    <h2>割り当て一覧</h2>
    <table>
        <thead><tr><th>ID</th><th>メールアドレス</th><th>割当日時</th><th>解除日時</th><th></th></tr></thead>
        <tbody>
        @forelse ($license->assignments as $a)
            <tr>
                <td>{{ $a->id }}</td>
                <td>{{ $a->user_email }}</td>
                <td>{{ $a->assigned_at?->format('Y-m-d H:i') }}</td>
                <td>{{ $a->released_at?->format('Y-m-d H:i') ?? '-' }}</td>
                <td>
                    @unless ($a->isReleased())
                        <form class="inline" method="post" action="{{ route('licenses.release', [$license, $a]) }}">
                            @csrf
                            <button type="submit">解除</button>
                        </form>
                    @endunless
                </td>
            </tr>
        @empty
            <tr><td colspan="5">割り当てはありません。</td></tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
