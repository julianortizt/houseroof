<?php

namespace App\Observers;

use App\Models\ContentBlock;

class ContentBlockObserver
{
    public function saved(ContentBlock $block): void
    {
        ContentBlock::flushCache();
    }

    public function deleted(ContentBlock $block): void
    {
        ContentBlock::flushCache();
    }
}
