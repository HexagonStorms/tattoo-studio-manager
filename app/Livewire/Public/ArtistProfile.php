<?php

namespace App\Livewire\Public;

use App\Models\Artist;
use App\Models\PortfolioImage;
use App\Models\Studio;
use App\Services\TenantService;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class ArtistProfile extends Component
{
    public Studio $studio;
    public Artist $artist;
    public string $selectedStyle = '';
    public ?PortfolioImage $selectedImage = null;
    public bool $showModal = false;

    public function mount(TenantService $tenantService, string $slug): void
    {
        $this->studio = $tenantService->current();

        $this->artist = Artist::query()
            ->where('studio_id', $this->studio->id)
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
    }

    public function getPortfolioImagesProperty(): Collection
    {
        $query = $this->artist->portfolioImages();

        if ($this->selectedStyle) {
            $query->where('style', $this->selectedStyle);
        }

        return $query->get();
    }

    public function getAvailableStylesProperty(): array
    {
        return $this->artist
            ->portfolioImages()
            ->whereNotNull('style')
            ->distinct()
            ->pluck('style')
            ->filter()
            ->sort()
            ->values()
            ->toArray();
    }

    public function filterByStyle(string $style): void
    {
        $this->selectedStyle = $this->selectedStyle === $style ? '' : $style;
    }

    public function clearFilter(): void
    {
        $this->selectedStyle = '';
    }

    public function openImage(int $imageId): void
    {
        $this->selectedImage = $this->artist->portfolioImages()->find($imageId);
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

        $images = $this->portfolioImages;
        $currentIndex = $images->search(fn($img) => $img->id === $this->selectedImage->id);

        if ($currentIndex !== false && $currentIndex < $images->count() - 1) {
            $this->selectedImage = $images[$currentIndex + 1];
        }
    }

    public function previousImage(): void
    {
        if (!$this->selectedImage) return;

        $images = $this->portfolioImages;
        $currentIndex = $images->search(fn($img) => $img->id === $this->selectedImage->id);

        if ($currentIndex !== false && $currentIndex > 0) {
            $this->selectedImage = $images[$currentIndex - 1];
        }
    }

    public function render()
    {
        return view('livewire.public.artist-profile', [
            'portfolioImages' => $this->portfolioImages,
            'availableStyles' => $this->availableStyles,
        ]);
    }
}
