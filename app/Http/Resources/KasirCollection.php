<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class KasirCollection extends ResourceCollection
{
    public $collects = KasirResource::class;

    protected $total;
    protected $page;
    protected $size;
    protected $pages;

    public function __construct($items, $total, $page, $size, $pages)
    {
        parent::__construct($items);
        $this->total = $total;
        $this->page = $page;
        $this->size = $size;
        $this->pages = $pages;
    }

    public function toArray(Request $request): array
    {
        return [
            'items' => $this->collection,
            'total' => (int) $this->total,
            'page' => (int) $this->page,
            'size' => (int) $this->size,
            'pages' => (int) $this->pages,
        ];
    }
}
