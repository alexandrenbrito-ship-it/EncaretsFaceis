<?php
class Paginator {
    private int $currentPage;
    private int $perPage;
    private int $total;
    private string $baseUrl;

    public function __construct(int $currentPage = 1, int $perPage = 15, int $total = 0) {
        $this->currentPage = max(1, $currentPage);
        $this->perPage = max(1, $perPage);
        $this->total = $total;
    }

    public function setBaseUrl(string $url): self {
        $this->baseUrl = $url;
        return $this;
    }

    public function getOffset(): int {
        return ($this->currentPage - 1) * $this->perPage;
    }

    public function getLimit(): int {
        return $this->perPage;
    }

    public function getTotalPages(): int {
        return (int) ceil($this->total / $this->perPage);
    }

    public function hasNext(): bool {
        return $this->currentPage < $this->getTotalPages();
    }

    public function hasPrevious(): bool {
        return $this->currentPage > 1;
    }

    public function nextPage(): int {
        return min($this->currentPage + 1, $this->getTotalPages());
    }

    public function previousPage(): int {
        return max($this->currentPage - 1, 1);
    }

    public function render(): string {
        if ($this->getTotalPages() <= 1) {
            return '';
        }

        $html = '<nav><ul class="pagination justify-content-center">';

        if ($this->hasPrevious()) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $this->baseUrl . '?page=' . $this->previousPage() . '">Anterior</a></li>';
        }

        for ($i = 1; $i <= $this->getTotalPages(); $i++) {
            if ($i === 1 || $i === $this->getTotalPages() || ($i >= $this->currentPage - 2 && $i <= $this->currentPage + 2)) {
                $active = $i === $this->currentPage ? ' active' : '';
                $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $this->baseUrl . '?page=' . $i . '">' . $i . '</a></li>';
            } elseif ($i === $this->currentPage - 3 || $i === $this->currentPage + 3) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        if ($this->hasNext()) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $this->baseUrl . '?page=' . $this->nextPage() . '">Próximo</a></li>';
        }

        $html .= '</ul></nav>';

        return $html;
    }

    public static function createFromRequest(int $perPage = 15): self {
        $page = (int)($_GET['page'] ?? 1);
        return new self($page, $perPage);
    }
}