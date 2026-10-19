<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ライセンス一覧</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 24px; color: #222; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #999; padding: 6px 10px; text-align: left; }
        th { background: #eee; }
        .status-expired { color: #a00; }
        .status-cancelled { color: #777; }
        form.inline { display: inline; }
        .note { color: #555; font-size: 0.9em; }
    </style>
</head>
<body>
    <h1>ライセンス一覧</h1>
    <p class="note">練習用の架空データです。<a href="{{ route('dashboard') }}">ダッシュボードへ戻る</a></p>

    <form method="get" action="{{ route('licenses.index') }}">
        <label>期限が近いものだけ表示: 本日から
            <input type="number" name="expiring_within" min="0" value="{{ $expiringWithin }}" style="width: 5em"> 日以内
        </label>
        <button type="submit">絞り込む</button>
        @if ($expiringWithin !== null)
            <a href="{{ route('licenses.index') }}">解除</a>
        @endif
    </form>

    <table>
        <thead>
            <tr><th>ID</th><th>顧客名</th><th>製品</th><th>座席</th><th>使用中</th><th>空き</th><th>開始日</th><th>有効期限</th><th>状態</th></tr>
        </thead>
        <tbody>
        @forelse ($licenses as $license)
            <tr>
                <td><a href="{{ route('licenses.show', $license) }}">{{ $license->id }}</a></td>
                <td>{{ $license->customer_name }}</td>
                <td>{{ $license->product_code }}</td>
                <td>{{ $license->seats }}</td>
                <td>{{ $license->seats_used }}</td>
                <td>{{ $license->remainingSeats() }}</td>
                <td>{{ $license->starts_on->format('Y-m-d') }}</td>
                <td>{{ $license->expires_on->format('Y-m-d') }}</td>
                <td class="status-{{ $license->status }}">{{ $license->status }}</td>
            </tr>
        @empty
            <tr><td colspan="9">該当するライセンスはありません。</td></tr>
        @endforelse
        </tbody>
    </table>

    <h2>新規登録</h2>
    @if ($errors->any())
        <ul style="color:#a00">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    @endif
    <form method="post" action="{{ route('licenses.store') }}">
        @csrf
        <label>顧客名 <input name="customer_name" required></label>
        <label>製品コード <input name="product_code" required value="WI-CORE"></label>
        <label>座席数 <input type="number" name="seats" min="1" required value="5"></label>
        <label>開始日 <input type="date" name="starts_on" required value="{{ today()->toDateString() }}"></label>
        <label>有効期限 <input type="date" name="expires_on" required value="{{ today()->addYear()->toDateString() }}"></label>
        <button type="submit">登録</button>
    </form>
</body>
</html>
