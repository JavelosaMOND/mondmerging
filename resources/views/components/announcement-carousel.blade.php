<div id="announcementCarousel" class="carousel slide h-100" data-bs-ride="carousel">
    <div class="carousel-indicators">
        @foreach($announcements as $index => $announcement)
            <button type="button" data-bs-target="#announcementCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}" aria-label="Slide {{ $index + 1 }}"></button>
        @endforeach
    </div>
    
    <div class="carousel-inner h-100">
        @foreach($announcements as $index => $announcement)
            <div class="carousel-item h-100 {{ $index === 0 ? 'active' : '' }}">
                @if($announcement->image_path)
                    <!-- Image Background Layout -->
                    <div class="carousel-background" style="
                        background-image: url('{{ asset('storage/' . $announcement->image_path) }}');
                        background-size: cover;
                        background-position: center;
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                    "></div>
                    
                    <div class="d-flex flex-column justify-content-end h-100">
                        <div class="announcement-content-overlay" style="
                            background: rgba(0, 0, 0, 0.5);
                            padding: 30px 40px;
                            backdrop-filter: blur(5px);
                        ">
                            <div class="container">
                                @if($announcement->category == 'recognition')
                                    <div class="announcement-badge">
                                        <i class="fas fa-award me-2"></i> Recognition
                                    </div>
                                @elseif($announcement->category == 'important_update')
                                    <div class="announcement-badge">
                                        <i class="fas fa-bell me-2"></i> Important Update
                                    </div>
                                @elseif($announcement->category == 'upcoming_event')
                                    <div class="announcement-badge">
                                        <i class="fas fa-calendar me-2"></i> Upcoming Event
                                    </div>
                                @else
                                    <div class="announcement-badge">
                                        <i class="fas fa-info-circle me-2"></i> Announcement
                                    </div>
                                @endif
                                
                                <h2 class="announcement-title mb-3" style="font-size: 28px;">{{ $announcement->title }}</h2>
                                <div class="announcement-text mb-3" style="
                                    font-size: 14px;
                                    max-height: 120px;
                                    overflow-y: auto;
                                    line-height: 1.5;
                                    padding-right: 5px;
                                ">
                                    {!! $announcement->content !!}
                                </div>
                                
                                @if($announcement->button_text && $announcement->button_link)
                                    <a href="{{ $announcement->button_link }}" class="btn btn-light mt-2 px-4" target="_blank" rel="noopener noreferrer">
                                        {{ $announcement->button_text }}
                                        <i class="fas fa-arrow-right ms-2"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Gradient Background Layout -->
                    <div class="carousel-background" style="
                        background: linear-gradient(135deg, {{ $announcement->background_color }} 0%, {{ $announcement->background_color == '#f8fafc' ? '#003366' : $announcement->background_color }} 100%);
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        z-index: 0;
                    "></div>
                    
                    <div class="overlay" style="
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: linear-gradient(135deg, 
                            rgba(0,51,102,0.75) 0%, 
                            rgba(0,25,50,0.9) 100%);
                        z-index: 1;
                        opacity: 0.6;
                    "></div>
                    
                    <div class="container d-flex align-items-center justify-content-center h-100">
                        <div class="announcement-content position-relative" style="z-index: 2; max-width: 800px;">
                            @if($announcement->category == 'recognition')
                                <div class="announcement-badge">
                                    <i class="fas fa-award me-2"></i> Recognition
                                </div>
                            @elseif($announcement->category == 'important_update')
                                <div class="announcement-badge">
                                    <i class="fas fa-bell me-2"></i> Important Update
                                </div>
                            @elseif($announcement->category == 'upcoming_event')
                                <div class="announcement-badge">
                                    <i class="fas fa-calendar me-2"></i> Upcoming Event
                                </div>
                            @else
                                <div class="announcement-badge">
                                    <i class="fas fa-info-circle me-2"></i> Announcement
                                </div>
                            @endif
                            
                            <div class="text-center">
                                <h2 class="announcement-title mb-4" style="font-size: 32px;">{{ $announcement->title }}</h2>
                                <div class="announcement-text mb-4" style="
                                    font-size: 15px;
                                    max-height: 200px;
                                    overflow-y: auto;
                                    line-height: 1.5;
                                ">
                                    {!! $announcement->content !!}
                                </div>
                                @if($announcement->button_text && $announcement->button_link)
                                    <a href="{{ $announcement->button_link }}" class="btn btn-light btn-lg mt-3 px-4" target="_blank" rel="noopener noreferrer">
                                        {{ $announcement->button_text }}
                                        <i class="fas fa-arrow-right ms-2"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
    
    <button class="carousel-control-prev" type="button" data-bs-target="#announcementCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#announcementCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>

<style>
    /* Custom scrollbar styling for the announcement text */
    .announcement-text::-webkit-scrollbar {
        width: 4px;
    }
    
    .announcement-text::-webkit-scrollbar-thumb {
        background-color: rgba(255, 255, 255, 0.3);
        border-radius: 4px;
    }
    
    .announcement-text::-webkit-scrollbar-track {
        background: transparent;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const carousel = new bootstrap.Carousel(document.getElementById('announcementCarousel'), {
            interval: 5000, // 5 seconds between slides
            wrap: true,
            keyboard: true,
            pause: 'hover'
        });
        
        // Ensure prev/next controls work
        document.querySelector('.carousel-control-prev').addEventListener('click', function() {
            carousel.prev();
        });
        
        document.querySelector('.carousel-control-next').addEventListener('click', function() {
            carousel.next();
        });
    });
</script> 