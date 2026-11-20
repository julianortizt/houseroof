<?php

namespace App\Observers;

use App\Models\ContentBlock;
use Illuminate\Support\Facades\Cache;

class ContentBlockObserver
{
    public function saved(ContentBlock $block): void
    {
        Cache::forget("content_block:{$block->page}:{$block->section}:{$block->key}:{$block->locale}");
    }

    public function deleted(ContentBlock $block): void
    {
        Cache::forget("content_block:{$block->page}:{$block->section}:{$block->key}:{$block->locale}");
    }
}
