(function () {
    'use strict';

    document.documentElement.classList.add('wf-home-js');

    var slider = document.querySelector('[data-home-slider]');
    if (slider) {
        var slides = Array.prototype.slice.call(slider.querySelectorAll('[data-home-slide]'));
        var dots = Array.prototype.slice.call(slider.querySelectorAll('[data-home-dot]'));
        var prev = slider.querySelector('[data-home-prev]');
        var next = slider.querySelector('[data-home-next]');
        var currentLabel = slider.querySelector('[data-home-current]');
        var progress = slider.querySelector('[data-home-progress]');
        var toggle = slider.querySelector('[data-home-toggle]');
        var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var active = 0;
        var timer = null;
        var interval = 5600;
        var paused = false;
        var manualPaused = false;
        var touchStartX = 0;
        var touchStartY = 0;

        function restartProgress() {
            if (!progress || reduceMotion || slides.length < 2 || paused || manualPaused) return;
            progress.classList.remove('is-running');
            void progress.offsetWidth;
            progress.classList.add('is-running');
        }

        function updateSlide(index, userInitiated) {
            if (!slides.length) return;
            active = (index + slides.length) % slides.length;

            slides.forEach(function (slide, slideIndex) {
                var isActive = slideIndex === active;
                slide.classList.toggle('is-active', isActive);
                slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
                if ('inert' in slide) slide.inert = !isActive;
            });

            dots.forEach(function (dot, dotIndex) {
                var isActive = dotIndex === active;
                dot.classList.toggle('is-active', isActive);
                dot.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            if (currentLabel) currentLabel.textContent = String(active + 1).padStart(2, '0');
            restartProgress();
            if (userInitiated) restartTimer();
        }

        function stopTimer() {
            if (timer) window.clearInterval(timer);
            timer = null;
            if (progress) progress.classList.remove('is-running');
        }

        function startTimer() {
            if (reduceMotion || slides.length < 2 || paused || manualPaused || document.hidden) return;
            stopTimer();
            restartProgress();
            timer = window.setInterval(function () {
                updateSlide(active + 1, false);
            }, interval);
        }

        function restartTimer() {
            stopTimer();
            startTimer();
        }

        if (prev) prev.addEventListener('click', function () { updateSlide(active - 1, true); });
        if (next) next.addEventListener('click', function () { updateSlide(active + 1, true); });

        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                updateSlide(Number(dot.getAttribute('data-home-dot') || 0), true);
            });
        });

        if (toggle) {
            toggle.addEventListener('click', function () {
                manualPaused = !manualPaused;
                toggle.setAttribute('aria-pressed', manualPaused ? 'true' : 'false');
                toggle.setAttribute('aria-label', manualPaused ? 'Play banner autoplay' : 'Pause banner autoplay');
                var icon = toggle.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-pause', !manualPaused);
                    icon.classList.toggle('fa-play', manualPaused);
                }
                if (manualPaused) stopTimer();
                else startTimer();
            });
        }

        slider.addEventListener('mouseenter', function () {
            paused = true;
            stopTimer();
        });

        slider.addEventListener('mouseleave', function () {
            paused = false;
            startTimer();
        });

        slider.addEventListener('focusin', function () {
            paused = true;
            stopTimer();
        });

        slider.addEventListener('focusout', function (event) {
            if (!slider.contains(event.relatedTarget)) {
                paused = false;
                startTimer();
            }
        });

        slider.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowLeft') updateSlide(active - 1, true);
            if (event.key === 'ArrowRight') updateSlide(active + 1, true);
        });

        slider.addEventListener('touchstart', function (event) {
            var touch = event.changedTouches && event.changedTouches[0];
            if (!touch) return;
            touchStartX = touch.clientX;
            touchStartY = touch.clientY;
        }, { passive: true });

        slider.addEventListener('touchend', function (event) {
            var touch = event.changedTouches && event.changedTouches[0];
            if (!touch) return;
            var deltaX = touch.clientX - touchStartX;
            var deltaY = touch.clientY - touchStartY;
            if (Math.abs(deltaX) > 42 && Math.abs(deltaX) > Math.abs(deltaY)) {
                updateSlide(active + (deltaX < 0 ? 1 : -1), true);
            }
        }, { passive: true });

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) stopTimer();
            else startTimer();
        });

        updateSlide(0, false);
        startTimer();
    }

    var videoSlider = document.querySelector('[data-video-slider]');
    if (videoSlider) {
        var videoTrack = videoSlider.querySelector('[data-video-track]');
        var videoSlides = Array.prototype.slice.call(videoSlider.querySelectorAll('[data-video-slide]'));
        var videoDots = Array.prototype.slice.call(videoSlider.querySelectorAll('[data-video-dot]'));
        var videoPrev = videoSlider.querySelector('[data-video-prev]');
        var videoNext = videoSlider.querySelector('[data-video-next]');
        var videoActive = 0;
        var videoTouchX = 0;
        var videoTouchY = 0;

        function resetVideoMedia(slide) {
            if (!slide) return;
            var frame = slide.querySelector('[data-video-frame]');
            var play = slide.querySelector('[data-video-play]');
            if (frame) {
                frame.innerHTML = '';
                frame.hidden = true;
            }
            if (play) play.hidden = false;
        }

        function openVideo(slide) {
            if (!slide) return;
            var play = slide.querySelector('[data-video-play]');
            var frame = slide.querySelector('[data-video-frame]');
            if (!play || !frame) return;
            var embed = String(play.getAttribute('data-embed') || '').trim();
            if (!/^https:\/\/www\.youtube\.com\/embed\/[A-Za-z0-9_-]{6,}/i.test(embed)) return;
            videoSlides.forEach(function (item) {
                if (item !== slide) resetVideoMedia(item);
            });
            play.hidden = true;
            frame.hidden = false;
            frame.innerHTML = '<iframe src="' + embed.replace(/&/g, '&amp;') + (embed.indexOf('?') === -1 ? '?' : '&') + 'autoplay=1&rel=0&modestbranding=1" title="Class video" loading="eager" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>';
        }

        function setVideo(index) {
            if (!videoSlides.length) return;
            var previous = videoActive;
            videoActive = (index + videoSlides.length) % videoSlides.length;
            if (previous !== videoActive) resetVideoMedia(videoSlides[previous]);
            if (videoTrack) videoTrack.style.transform = 'translate3d(-' + (videoActive * 100) + '%,0,0)';
            videoSlides.forEach(function (slide, i) {
                var activeSlide = i === videoActive;
                slide.classList.toggle('is-active', activeSlide);
                slide.setAttribute('aria-hidden', activeSlide ? 'false' : 'true');
                if ('inert' in slide) slide.inert = !activeSlide;
            });
            videoDots.forEach(function (dot, i) {
                var activeDot = i === videoActive;
                dot.classList.toggle('is-active', activeDot);
                dot.setAttribute('aria-selected', activeDot ? 'true' : 'false');
            });
        }

        videoSlides.forEach(function (slide, index) {
            var play = slide.querySelector('[data-video-play]');
            if (play) play.addEventListener('click', function () { openVideo(slide); });
            var copyPlay = videoSlider.querySelector('[data-video-play-copy][data-slide-index="' + index + '"]');
            if (copyPlay) copyPlay.addEventListener('click', function () { openVideo(slide); });
        });
        if (videoPrev) videoPrev.addEventListener('click', function () { setVideo(videoActive - 1); });
        if (videoNext) videoNext.addEventListener('click', function () { setVideo(videoActive + 1); });
        videoDots.forEach(function (dot) {
            dot.addEventListener('click', function () { setVideo(Number(dot.getAttribute('data-video-dot') || 0)); });
        });
        videoSlider.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowLeft') { event.preventDefault(); setVideo(videoActive - 1); }
            if (event.key === 'ArrowRight') { event.preventDefault(); setVideo(videoActive + 1); }
        });
        videoSlider.addEventListener('touchstart', function (event) {
            var touch = event.changedTouches && event.changedTouches[0];
            if (!touch) return;
            videoTouchX = touch.clientX;
            videoTouchY = touch.clientY;
        }, { passive: true });
        videoSlider.addEventListener('touchend', function (event) {
            var touch = event.changedTouches && event.changedTouches[0];
            if (!touch) return;
            var dx = touch.clientX - videoTouchX;
            var dy = touch.clientY - videoTouchY;
            if (Math.abs(dx) > 45 && Math.abs(dx) > Math.abs(dy)) setVideo(videoActive + (dx < 0 ? 1 : -1));
        }, { passive: true });
        setVideo(0);
    }

    var revealItems = Array.prototype.slice.call(document.querySelectorAll('[data-reveal]'));
    if (!revealItems.length) return;

    if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        revealItems.forEach(function (item) { item.classList.add('is-visible'); });
        return;
    }

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -7% 0px' });

    revealItems.forEach(function (item) { observer.observe(item); });
}());
