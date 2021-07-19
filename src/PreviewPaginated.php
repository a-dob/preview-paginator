<?php

namespace Adob\PreviewPaginator;


use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;

trait PreviewPaginated
{

    protected  function getInitialQuantity()
    {
        return $this->initialQuantity ?: 5;
    }

    /**
     * @param Builder  $query
     * @param null     $initialQuantity
     * @param null     $perPage
     * @param string[] $columns
     * @param string   $pageName
     * @param null     $currentPage
     * @return mixed|object
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function scopePreviewPaginate(Builder $query, $initialQuantity = null, $perPage = null, $columns = ['*'], $pageName = 'page', $currentPage = null)
    {
        $currentPage = $currentPage ?: PreviewPaginator::resolveCurrentPage($pageName);

        $loadMore = $perPage ?: $this->model->getPerPage();

        $initialQuantity = $initialQuantity ?: $this->getInitialQuantity();

        $total = $query->count();

        $loadMore = ($currentPage == 1) ? $initialQuantity : $loadMore;
        $skip = ($currentPage == 1) ? 0 : ($initialQuantity + ($loadMore * ($currentPage - 2)));
        // Next we will set the limit and offset for this query so that when we get the
        // results we get the proper section of results. Then, we'll create the full
        // paginator instances for these results with the given page, per page and initial quantity on first page.
        $query->skip($skip)->take($loadMore);


        $items = $query->get($columns);
        $options = [
            'path' => PreviewPaginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ];
        return Container::getInstance()->makeWith(PreviewPaginator::class, compact(
            'items', 'total', 'initialQuantity', 'perPage', 'currentPage', 'options'
        ));
    }
}
