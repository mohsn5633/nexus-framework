<?php

namespace Nexus\Support;

/**
 * Paginator Class
 *
 * Handles pagination of database results with metadata
 */
class Paginator
{
    protected array $items;
    protected int $total;
    protected int $perPage;
    protected int $currentPage;
    protected string $path;
    protected array $query = [];

    /**
     * @param array $items Items for the current page
     * @param int $total Total number of items
     * @param int $perPage Items per page
     * @param int $currentPage Current page number
     * @param array $options Additional options (path, query)
     */
    public function __construct(
        array $items,
        int $total,
        int $perPage,
        int $currentPage = 1,
        array $options = []
    ) {
        $this->items = $items;
        $this->total = $total;
        $this->perPage = $perPage;
        $this->currentPage = $currentPage;
        $this->path = $options['path'] ?? $this->getCurrentPath();
        $this->query = $options['query'] ?? [];
    }

    /**
     * Get the items for the current page
     *
     * @return array
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * Get the total number of items
     *
     * @return int
     */
    public function total(): int
    {
        return $this->total;
    }

    /**
     * Get the number of items per page
     *
     * @return int
     */
    public function perPage(): int
    {
        return $this->perPage;
    }

    /**
     * Get the current page number
     *
     * @return int
     */
    public function currentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Get the last page number
     *
     * @return int
     */
    public function lastPage(): int
    {
        return (int) ceil($this->total / $this->perPage);
    }

    /**
     * Determine if there are more pages
     *
     * @return bool
     */
    public function hasMorePages(): bool
    {
        return $this->currentPage < $this->lastPage();
    }

    /**
     * Check if on first page
     *
     * @return bool
     */
    public function onFirstPage(): bool
    {
        return $this->currentPage === 1;
    }

    /**
     * Check if on last page
     *
     * @return bool
     */
    public function onLastPage(): bool
    {
        return $this->currentPage === $this->lastPage();
    }

    /**
     * Get the first item number on the current page
     *
     * @return int|null
     */
    public function firstItem(): ?int
    {
        if ($this->total === 0) {
            return null;
        }

        return ($this->currentPage - 1) * $this->perPage + 1;
    }

    /**
     * Get the last item number on the current page
     *
     * @return int|null
     */
    public function lastItem(): ?int
    {
        if ($this->total === 0) {
            return null;
        }

        return min($this->firstItem() + $this->perPage - 1, $this->total);
    }

    /**
     * Get URL for a given page number
     *
     * @param int $page
     * @return string
     */
    public function url(int $page): string
    {
        if ($page <= 0) {
            $page = 1;
        }

        $parameters = array_merge($this->query, ['page' => $page]);
        $query = http_build_query($parameters);

        return $this->path . ($query ? '?' . $query : '');
    }

    /**
     * Get URL for the next page
     *
     * @return string|null
     */
    public function nextPageUrl(): ?string
    {
        if ($this->hasMorePages()) {
            return $this->url($this->currentPage + 1);
        }

        return null;
    }

    /**
     * Get URL for the previous page
     *
     * @return string|null
     */
    public function previousPageUrl(): ?string
    {
        if ($this->currentPage > 1) {
            return $this->url($this->currentPage - 1);
        }

        return null;
    }

    /**
     * Get an array representation of the paginator
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'data' => $this->items,
            'current_page' => $this->currentPage,
            'first_page_url' => $this->url(1),
            'from' => $this->firstItem(),
            'last_page' => $this->lastPage(),
            'last_page_url' => $this->url($this->lastPage()),
            'next_page_url' => $this->nextPageUrl(),
            'path' => $this->path,
            'per_page' => $this->perPage,
            'prev_page_url' => $this->previousPageUrl(),
            'to' => $this->lastItem(),
            'total' => $this->total,
        ];
    }

    /**
     * Get JSON representation
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Render pagination links
     *
     * @param int $onEachSide Number of pages to show on each side
     * @return string
     */
    public function links(int $onEachSide = 3): string
    {
        if ($this->lastPage() <= 1) {
            return '';
        }

        $html = '<nav><ul class="pagination">';

        // Previous page link
        if ($this->onFirstPage()) {
            $html .= '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . $this->previousPageUrl() . '">Previous</a></li>';
        }

        // Page links
        $start = max(1, $this->currentPage - $onEachSide);
        $end = min($this->lastPage(), $this->currentPage + $onEachSide);

        if ($start > 1) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $this->url(1) . '">1</a></li>';
            if ($start > 2) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        for ($page = $start; $page <= $end; $page++) {
            if ($page === $this->currentPage) {
                $html .= '<li class="page-item active"><span class="page-link">' . $page . '</span></li>';
            } else {
                $html .= '<li class="page-item"><a class="page-link" href="' . $this->url($page) . '">' . $page . '</a></li>';
            }
        }

        if ($end < $this->lastPage()) {
            if ($end < $this->lastPage() - 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            $html .= '<li class="page-item"><a class="page-link" href="' . $this->url($this->lastPage()) . '">' . $this->lastPage() . '</a></li>';
        }

        // Next page link
        if ($this->hasMorePages()) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $this->nextPageUrl() . '">Next</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">Next</span></li>';
        }

        $html .= '</ul></nav>';

        return $html;
    }

    /**
     * Get the current path from the request
     *
     * @return string
     */
    protected function getCurrentPath(): string
    {
        return strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
    }

    /**
     * Convert paginator to string (renders links)
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->links();
    }
}
