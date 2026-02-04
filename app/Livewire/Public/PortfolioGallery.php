<?php

namespace App\Livewire\Public;

use App\Models\PortfolioImage;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class PortfolioGallery extends Component
{
    public Collection $images;
    public bool $filterable = false;
    public string $selectedStyle = '';
    public ?PortfolioImage $selectedImage = null;
    public bool $showModal = false;

    public function mount(Collection $images, bool $filterable = false): void
    {
        $this->images = $images;
        $this->filterable = $filterable;
    }

    public function getFilteredImagesProperty(): Collection
    {
        if (!$this->selectedStyle || !$this->filterable) {
            return $this->images;
        }

        return $this->images->filter(fn($image) => $image->style === $this->selectedStyle);
    }

    public function getAvailableStylesProperty(): array
    {
        if (!$this->filterable) {
            return [];
        }

        return $this->images
            ->pluck('style')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    public function filterByStyle(string $style): void
    {
        if (!$this->filterable) return;

        $this->selectedStyle = $this->selectedStyle === $style ? '' : $style;
    }

    public function clearFilter(): void
    {
        $this->selectedStyle = '';
    }

    public function openImage(int $imageId): void
    {
        $this->selectedImage = $this->images->firstWhere('id', $imageId);
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedImage = null;
    }

    public function nextImage(): void
    {
        if (!$this->selectedImage) return;

        $filtered = $this->filteredImages;
        $currentIndex = $filtered->search(fn($img) => $img->id === $this->selectedImage->id);

        if ($currentIndex !== false && $currentIndex < $filtered->count() - 1) {
            $this->selectedImage = $filtered->values()[$currentIndex + 1];
        }
    }

    public function previousImage(): void
    {
        if (!$this->selectedImage) return;

        $filtered = $this->filteredImages;
        $currentIndex = $filtered->search(fn($img) => $img->id === $this->selectedImage->id);

        if ($currentIndex !== false && $currentIndex > 0) {
            $this->selectedImage = $filtered->values()[$currentIndex - 1];
        }
    }

    public function render()
    {
        return view('livewire.public.portfolio-gallery', [
            'filteredImages' => $this->filteredImages,
            'availableStyles' => $this->availableStyles,
        ]);
    }
}
