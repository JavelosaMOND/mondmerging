@php
    $announcements = [];
    try {
        $announcements = \App\Models\Announcement::active()
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    } catch (\Exception $e) {
        // Silently handle any errors
    }
@endphp

<style>
    .sidebar-announcements {
        padding-top: 1rem;
        border-top: 1px solid var(--gray-200);
        margin-bottom: 1rem;
    }
    
    .announcements-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        padding: 0 10px;
    }
    
    .announcements-slider {
        position: relative;
        border-radius: 8px;
        background-color: #f5f5f5;
        margin: 0 10px;
        padding: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    
    .slider-wrapper {
        position: relative;
        min-height: 150px;
    }
    
    .sidebar-announcement {
        display: flex;
        flex-direction: column;
    }
    
    .announcement-content-wrapper {
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    
    .announcement-title {
        font-weight: 600;
        font-size: 16px;
        color: #333;
        margin-bottom: 12px;
        line-height: 1.3;
        padding-right: 30px; /* Make room for category badge */
    }
    
    .announcement-image {
        width: 100%;
        height: 120px;
        border-radius: 6px;
        overflow: hidden;
        margin-bottom: 12px;
        object-fit: cover;
    }
    
    .announcement-date {
        font-size: 12px;
        color: #666;
        margin-top: auto;
    }
    
    .announcement-category {
        position: absolute;
        top: 0;
        right: 0;
        z-index: 2;
    }
    
    /* Navigation arrows */
    .slider-nav {
        position: absolute;
        width: 32px;
        height: 32px;
        background-color: var(--primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        z-index: 5;
        font-size: 14px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }
    
    .slider-nav.prev {
        left: -16px;
    }
    
    .slider-nav.next {
        right: -16px;
    }
    
    /* Dots navigation */
    .slider-dots {
        display: flex;
        justify-content: center;
        margin-top: 14px;
        gap: 6px;
    }
    
    .slider-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #ddd;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    
    .slider-dot.active {
        background-color: var(--primary);
    }
    
    /* Fix for the modals */
    .modal-announcement {
        z-index: 1050 !important;
    }
    
    .modal-backdrop {
        z-index: 1040 !important;
    }
    
    /* Fix for the tooltip */
    .announcement-link {
        transition: all 0.2s ease;
        cursor: pointer;
        position: relative;
    }
    
    .announcement-link:hover {
        transform: translateY(-2px);
    }
</style>

@if(count($announcements) > 0)
    <div class="sidebar-announcements">
        <div class="announcements-header">
            <h6 class="text-muted fw-semibold fs-7 text-uppercase m-0">
                <i class="fas fa-bullhorn me-2"></i> Announcements
            </h6>
            <span class="badge bg-primary">{{ count($announcements) }}</span>
        </div>
        
        <div class="announcements-slider" id="announcements-slider">
            @if(count($announcements) > 1)
                <div class="slider-nav prev" onclick="prevAnnouncement()">
                    <i class="fas fa-chevron-left"></i>
                </div>
                <div class="slider-nav next" onclick="nextAnnouncement()">
                    <i class="fas fa-chevron-right"></i>
                </div>
            @endif
            
            <div class="slider-wrapper" id="slider-wrapper">
                @foreach($announcements as $index => $announcement)
                    <div class="sidebar-announcement" data-index="{{ $index }}" style="display: {{ $index == 0 ? 'block' : 'none' }};" onclick="openAnnouncementModal({{ $announcement->id }})">
                        <div class="announcement-content-wrapper">
                            <div class="announcement-category">
                                @if($announcement->category == 'recognition')
                                    <span class="badge bg-success">
                                        <i class="fas fa-award"></i>
                                    </span>
                                @elseif($announcement->category == 'important_update')
                                    <span class="badge bg-danger">
                                        <i class="fas fa-bell"></i>
                                    </span>
                                @elseif($announcement->category == 'upcoming_event')
                                    <span class="badge bg-primary">
                                        <i class="fas fa-calendar"></i>
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-info-circle"></i>
                                    </span>
                                @endif
                            </div>
                            
                            <div class="announcement-title">{{ $announcement->title }}</div>
                            
                            @if($announcement->image_path)
                                <img src="{{ asset('storage/' . $announcement->image_path) }}" 
                                     alt="{{ $announcement->title }}"
                                     class="announcement-image">
                            @endif
                            
                            <div class="announcement-date">{{ $announcement->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            @if(count($announcements) > 1)
                <div class="slider-dots" id="slider-dots">
                    @foreach($announcements as $index => $announcement)
                        <div class="slider-dot {{ $index == 0 ? 'active' : '' }}" data-index="{{ $index }}" onclick="goToAnnouncement({{ $index }})"></div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    
    <!-- Modals container (outside the sidebar to prevent nesting issues) -->
    <div class="announcement-modals">
        @foreach($announcements as $announcement)
            <div class="modal fade modal-announcement" id="modal-announcement-{{ $announcement->id }}" tabindex="-1" aria-labelledby="announcementModalLabel-{{ $announcement->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color: {{ $announcement->background_color ?? '#f8fafc' }}; color: #fff;">
                            <h5 class="modal-title" id="announcementModalLabel-{{ $announcement->id }}">{{ $announcement->title }}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="closeAnnouncementModal({{ $announcement->id }})"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                @if($announcement->image_path)
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <img src="{{ asset('storage/' . $announcement->image_path) }}" 
                                            alt="{{ $announcement->title }}"
                                            class="img-fluid rounded">
                                    </div>
                                @endif
                                <div class="col-md-{{ $announcement->image_path ? '8' : '12' }}">
                                    <div class="announcement-content">
                                        {!! $announcement->content !!}
                                    </div>
                                    
                                    @if($announcement->button_text && $announcement->button_link)
                                        <div class="mt-3">
                                            <a href="{{ $announcement->button_link }}" class="btn btn-primary" target="_blank" rel="noopener noreferrer">
                                                {{ $announcement->button_text }}
                                                <i class="fas fa-external-link-alt ms-2"></i>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeAnnouncementModal({{ $announcement->id }})">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        // Announcement slider variables
        let currentAnnouncementIndex = 0;
        const totalAnnouncements = {{ count($announcements) }};
        
        // Slider navigation functions
        function showAnnouncement(index) {
            // Hide all announcements
            document.querySelectorAll('.sidebar-announcement').forEach(el => {
                el.style.display = 'none';
            });
            
            // Show the selected announcement
            const announcement = document.querySelector(`.sidebar-announcement[data-index="${index}"]`);
            if (announcement) {
                announcement.style.display = 'block';
            }
            
            // Update dots
            document.querySelectorAll('.slider-dot').forEach(dot => {
                dot.classList.remove('active');
            });
            const activeDot = document.querySelector(`.slider-dot[data-index="${index}"]`);
            if (activeDot) {
                activeDot.classList.add('active');
            }
            
            // Update current index
            currentAnnouncementIndex = index;
        }
        
        function nextAnnouncement() {
            let nextIndex = currentAnnouncementIndex + 1;
            if (nextIndex >= totalAnnouncements) {
                nextIndex = 0;
            }
            showAnnouncement(nextIndex);
        }
        
        function prevAnnouncement() {
            let prevIndex = currentAnnouncementIndex - 1;
            if (prevIndex < 0) {
                prevIndex = totalAnnouncements - 1;
            }
            showAnnouncement(prevIndex);
        }
        
        function goToAnnouncement(index) {
            showAnnouncement(index);
        }
        
        // Auto rotate announcements every 5 seconds
        let slideInterval;
        
        function startSlideTimer() {
            if (totalAnnouncements > 1) {
                slideInterval = setInterval(nextAnnouncement, 5000);
            }
        }
        
        function stopSlideTimer() {
            clearInterval(slideInterval);
        }
        
        // Modal handling functions
        function openAnnouncementModal(id) {
            // First close any open modals and remove backdrops
            closeAllModals();
            
            // Now open the requested modal
            const modalId = 'modal-announcement-' + id;
            const modalElement = document.getElementById(modalId);
            
            if (modalElement) {
                // Create modal instance manually
                const modal = new bootstrap.Modal(modalElement, {
                    backdrop: 'static',
                    keyboard: false
                });
                
                // Show the modal
                modal.show();
                
                // Add event listener for when the modal is hidden
                modalElement.addEventListener('hidden.bs.modal', function() {
                    document.body.classList.remove('modal-open');
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(backdrop => backdrop.remove());
                });
            }
        }
        
        function closeAnnouncementModal(id) {
            const modalId = 'modal-announcement-' + id;
            const modalElement = document.getElementById(modalId);
            
            if (modalElement) {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }
            }
            
            // Ensure backdrop is removed
            setTimeout(() => {
                document.body.classList.remove('modal-open');
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => backdrop.remove());
            }, 100);
        }
        
        function closeAllModals() {
            // Get all open modals
            const openModals = document.querySelectorAll('.modal.show');
            openModals.forEach(modalElement => {
                if (modalElement) {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }
                }
            });
            
            // Remove modal-open class from body
            document.body.classList.remove('modal-open');
            
            // Remove any backdrop elements
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());
        }
        
        // Move modals to the body to prevent nesting issues
        document.addEventListener('DOMContentLoaded', function() {
            const modalsContainer = document.querySelector('.announcement-modals');
            if (modalsContainer) {
                const modalElements = modalsContainer.querySelectorAll('.modal');
                modalElements.forEach(element => {
                    document.body.appendChild(element);
                });
                modalsContainer.remove();
            }
            
            // Add hover event to pause/resume the slides
            const slider = document.getElementById('announcements-slider');
            if (slider) {
                slider.addEventListener('mouseenter', stopSlideTimer);
                slider.addEventListener('mouseleave', startSlideTimer);
            }
            
            // Start the auto rotation
            startSlideTimer();
            
            // Add keyboard navigation
            document.addEventListener('keydown', function(e) {
                // Check if any modal is open
                const openModal = document.querySelector('.modal.show');
                
                // If Escape key is pressed and a modal is open, close it
                if (e.key === 'Escape' && openModal) {
                    const modalId = openModal.id;
                    const announcementId = modalId.split('-').pop();
                    closeAnnouncementModal(announcementId);
                    return;
                }
                
                // Only respond if the announcements are in view
                const slider = document.getElementById('announcements-slider');
                if (slider && isElementInViewport(slider)) {
                    if (e.key === 'ArrowLeft') {
                        prevAnnouncement();
                    } else if (e.key === 'ArrowRight') {
                        nextAnnouncement();
                    }
                }
            });
        });
        
        // Utility function to check if element is in viewport
        function isElementInViewport(el) {
            const rect = el.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        }
    </script>
@endif 