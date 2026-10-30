'use strict';
 
const PREF_KEY = 'studenthub.prefs';
 
function loadPrefs() {
    try {
        return JSON.parse(localStorage.getItem(PREF_KEY)) || {};
    } catch (error) {
        return {};
    }
}
 
function savePref(key, value) {
    const prefs = loadPrefs();
    prefs[key] = value;
    try {
        localStorage.setItem(PREF_KEY, JSON.stringify(prefs));
    } catch (error) {
 
    }
}
 
document.documentElement.classList.add('js');
document.documentElement.dataset.theme = loadPrefs().theme || 'light';
 
function initTheme() {
    const toggle = document.getElementById('theme-toggle');
    if (!toggle) return;
 
    function reflect() {
        const dark = document.documentElement.dataset.theme === 'dark';
        toggle.setAttribute('aria-pressed', String(dark));
        toggle.textContent = dark ? 'Light mode' : 'Dark mode';
    }
 
    toggle.addEventListener('click', function () {
        const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.theme = next;
        savePref('theme', next);
        reflect();
    });
    reflect();
}
 
function initMenu() {
    const button = document.getElementById('menu-toggle');
    const nav = document.getElementById('main-nav');
    if (!button || !nav) return;
 
    function setOpen(open) {
        nav.classList.toggle('open', open);
        button.setAttribute('aria-expanded', String(open));
        button.textContent = open ? 'Close' : 'Menu';
    }
 
    button.addEventListener('click', function () {
        setOpen(!nav.classList.contains('open'));
    });
 
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && nav.classList.contains('open')) {
            setOpen(false);
            button.focus();
        }
    });
}
 
function initBanner() {
    const banner = document.getElementById('notice-banner');
    const close = document.getElementById('banner-close');
    if (!banner || !close) return;
 
    if (loadPrefs().bannerDismissed) {
        banner.hidden = true;
    }
    close.addEventListener('click', function () {
        banner.hidden = true;
        savePref('bannerDismissed', true);
    });
}
 
function initAccordion() {
    document.addEventListener('click', function (event) {
        const button = event.target.closest('.accordion-btn');
        if (!button) return;
 
        const panel = document.getElementById(button.getAttribute('aria-controls'));
        const isOpen = button.getAttribute('aria-expanded') === 'true';
        button.setAttribute('aria-expanded', String(!isOpen));
        if (panel) panel.hidden = isOpen;
    });
}
 
function initModal() {
    let backdrop = null;
    let lastFocused = null;
 
    function close() {
        if (!backdrop) return;
        backdrop.remove();
        backdrop = null;
        document.removeEventListener('keydown', onKey);
        if (lastFocused) lastFocused.focus();
    }
 
    function onKey(event) {
        if (event.key === 'Escape') close();
        if (event.key === 'Tab' && backdrop) {
 
            const focusable = backdrop.querySelectorAll('button');
            event.preventDefault();
            focusable[0].focus();
        }
    }
 
    function open(title, text) {
        close();
        lastFocused = document.activeElement;
 
        backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop';
        backdrop.innerHTML =
            '<div class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">' +
            '<h2 id="modal-title"></h2><p id="modal-text"></p>' +
            '<button type="button" class="modal-close">Close</button></div>';
        backdrop.querySelector('#modal-title').textContent = title;
        backdrop.querySelector('#modal-text').textContent = text;
        document.body.appendChild(backdrop);
 
        backdrop.querySelector('.modal-close').addEventListener('click', close);
        backdrop.addEventListener('click', function (event) {
            if (event.target === backdrop) close();
        });
        document.addEventListener('keydown', onKey);
        backdrop.querySelector('.modal-close').focus();
    }
 
    document.addEventListener('click', function (event) {
        const trigger = event.target.closest('[data-modal-title]');
        if (trigger) {
            open(trigger.dataset.modalTitle, trigger.dataset.modalText || '');
        }
    });
}
 
function initSlider(slider) {
    const slides = Array.from(slider.querySelectorAll('.slide'));
    const dotsBox = slider.querySelector('.slider-dots');
    const pauseButton = slider.querySelector('.slider-pause');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let index = 0;
    let timer = null;
    let paused = reduceMotion;
 
    const dots = slides.map(function (slide, i) {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.setAttribute('aria-label', 'Go to slide ' + (i + 1));
        dot.addEventListener('click', function () { show(i); });
        dotsBox.appendChild(dot);
        return dot;
    });
 
    function show(i) {
        index = (i + slides.length) % slides.length;
        slides.forEach(function (slide, n) {
            slide.classList.toggle('is-active', n === index);
        });
        dots.forEach(function (dot, n) {
            dot.setAttribute('aria-current', String(n === index));
        });
    }
 
    function start() {
        stop();
        if (!paused) timer = setInterval(function () { show(index + 1); }, 5000);
    }
 
    function stop() {
        clearInterval(timer);
    }
 
    slider.querySelector('.slider-prev').addEventListener('click', function () { show(index - 1); start(); });
    slider.querySelector('.slider-next').addEventListener('click', function () { show(index + 1); start(); });
 
    pauseButton.addEventListener('click', function () {
        paused = !paused;
        pauseButton.setAttribute('aria-pressed', String(paused));
        pauseButton.textContent = paused ? 'Play' : 'Pause';
        start();
    });
    pauseButton.setAttribute('aria-pressed', String(paused));
    pauseButton.textContent = paused ? 'Play' : 'Pause';
 
    slider.addEventListener('mouseenter', stop);
    slider.addEventListener('mouseleave', start);
    slider.addEventListener('focusin', stop);
    slider.addEventListener('focusout', start);
 
    show(0);
    start();
}
 
document.addEventListener('DOMContentLoaded', function () {
    initTheme();
    initMenu();
    initBanner();
    initAccordion();
    initModal();
    document.querySelectorAll('.slider').forEach(initSlider);
});