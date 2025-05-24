<?php

namespace App\Services;

final class Toast
{
    public function make($type, $title, $message, $link1 = null, $link2 = null, $timeout = 5000): void
    {
        session()->flash('toasts', [
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link1' => $link1,
            'link2' => $link2,
            'timeout' => $timeout,
            'date' => now()->timestamp,
        ]);
    }

    public static function success($title, $message = '', $link1 = null, $link2 = null, $timeout = 5000): void
    {
        (new self)->make('success', $title, $message, $link1, $link2, $timeout);
    }

    public static function info($title, $message = '', $link1 = null, $link2 = null, $timeout = 5000): void
    {
        (new self)->make('info', $title, $message, $link1, $link2, $timeout);
    }

    public static function warning($title, $message = '', $link1 = null, $link2 = null, $timeout = 5000): void
    {
        (new self)->make('warning', $title, $message, $link1, $link2, $timeout);
    }

    public static function error($title, $message = '', $link1 = null, $link2 = null, $timeout = 5000): void
    {
        (new self)->make('error', $title, $message, $link1, $link2, $timeout);
    }
}
