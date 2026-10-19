<?php

/*
 * Livewire 4 の既定レイアウトは layouts::app だが、スターターキットのレイアウトは
 * resources/views/components/layouts/app.blade.php にあるため、ここで合わせる。
 * (設定画面の Volt コンポーネントがレイアウト未指定のため、これが無いと 500 になる)
 */
return [
    'component_layout' => 'components.layouts.app',
];
