<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

/** 業務ルール違反(空き座席なし、二重割り当てなど)。画面では入力エラーとして表示し、JSON では 422 を返す */
class LicenseException extends RuntimeException
{
    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return response()->json(['message' => $this->getMessage()], 422);
        }

        return back()->withErrors(['license' => $this->getMessage()]);
    }
}
