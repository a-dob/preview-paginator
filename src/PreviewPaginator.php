<?php

namespace Adob\PreviewPaginator;

use ArrayAccess;
use Countable;
use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use IteratorAggregate;
use JsonSerializable;
use ReturnTypeWillChange;

class PreviewPaginator extends AbstractPaginator implements Arrayable, ArrayAccess, Countable, IteratorAggregate, Jsonable, JsonSerializable, PaginatorContract
{
    /**
     * Determine if there are more items in the data source.
     *
     * @return bool
     */
    protected $hasMore;

    /**
     * The total number of items before slicing.
     *
     * @var int
     */
    protected $total;

    /**
     * Initial quantity on first page.
     *
     * @return int
     */
    protected $initialQuantity;

    /**
     * Create a new paginator instance.
     *
     * @param mixed    $items
     * @param int      $total
     * @param int      $initialQuantity
     * @param int      $perPage
     * @param int|null $currentPage
     * @param array    $options (path, query, fragment, pageName)
     */
    public function __construct($items, int $total, int $initialQuantity, int $perPage, int $currentPage = null, array $options = [])
    {
        $this->options = $options;

        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }
        $this->initialQuantity = $initialQuantity;
        $this->total = $total;
        $this->perPage = $perPage;
        $this->currentPage = $this->setCurrentPage($currentPage);
        $this->path = $this->path !== '/' ? rtrim($this->path, '/') : $this->path;

        $this->setItems($items);
    }

    /**
     * Get the current page for the request.
     *
     * @param int $currentPage
     * @return int
     */
    protected function setCurrentPage($currentPage)
    {
        $currentPage = $currentPage ?: static::resolveCurrentPage();

        return $this->isValidPageNumber($currentPage) ? (int)$currentPage : 1;
    }

    /**
     * Set the items for the paginator.
     *
     * @param mixed $items
     * @return void
     */
    protected function setItems($items)
    {
        $this->items = $items instanceof Collection ? $items : Collection::make($items);

        $this->hasMore = $this->lastItem() < $this->total();

        $this->items = $this->items->slice(0, $this->pageSize());
    }

    /**
     * Get the current number of entries on the page
     *
     * @return int
     */
    protected function pageSize()
    {
        return $this->currentPage == 1 ? $this->initialQuantity : $this->perPage;
    }

    /**
     * Render the paginator using the given view.
     *
     * @param string|null $view
     * @param array       $data
     * @return string
     */
    public function links($view = null, $data = [])
    {
        return $this->render($view, $data);
    }

    /**
     * Render the paginator using the given view.
     *
     * @param string|null $view
     * @param array       $data
     * @return \Illuminate\Contracts\Support\Htmlable
     */
    public function render($view = null, $data = [])
    {
        return static::viewFactory()->make($view ?: static::$defaultSimpleView, array_merge($data, [
            'paginator' => $this,
        ]));
    }

    /**
     * Manually indicate that the paginator does have more pages.
     *
     * @param bool $hasMore
     * @return $this
     */
    public function hasMorePagesWhen($hasMore = true)
    {
        $this->hasMore = $hasMore;

        return $this;
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'data' => $this->items->toArray(),
            'current_page' => $this->currentPage(),
            'path' => $this->path(),
            'first_page_url' => $this->url(1),
            'prev_page_url' => $this->previousPageUrl(),
            'next_page_url' => $this->nextPageUrl(),
            'per_page' => $this->perPage(),
            'initial_quantity' => $this->getInitialQuantity(),
            'total' => $this->total(),
            'from' => $this->firstItem(),
            'to' => $this->lastItem(),
        ];
    }

    /**
     * Get the URL for the next page.
     *
     * @return string|null
     */
    public function nextPageUrl()
    {
        if ($this->hasMorePages()) {
            return $this->url($this->currentPage() + 1);
        }
    }

    /**
     * Determine if there are more items in the data source.
     *
     * @return bool
     */
    public function hasMorePages()
    {
        return $this->hasMore;
    }

    /**
     * Get the number of models to return first page.
     *
     * @return int
     */
    protected function getInitialQuantity()
    {
        return $this->initialQuantity;
    }

    /**
     * Get the number of models to return first page.
     *
     * @return int
     */
    protected function setInitialQuantity($initialQuantity)
    {
        return $this->initialQuantity;
    }

    /**
     * Get the total number of items being paginated.
     *
     * @return int
     */
    public function total()
    {
        return $this->total;
    }

    /**
     * Get the number of the first item in the slice.
     *
     * @return int
     */
    public function firstItem()
    {
        return ($this->currentPage == 1) ? 1 : ($this->initialQuantity + ($this->currentPage - 2) * $this->perPage + 1);
    }

    /**
     * Get the number of the last item in the slice.
     *
     * @return int
     */
    public function lastItem()
    {
        return count($this->items) > 0 ? $this->firstItem() + $this->count() - 1 : null;
    }

    /**
     * Get the number of skipped items
     *
     * @return int
     */
    public function skippedItems()
    {
        return ($this->currentPage == 1) ? 0 : ($this->initialQuantity + ($this->pageSize() * ($this->currentPage - 2)));
    }
}
