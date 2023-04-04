<script>
function changeBlocksOnPageReload(container, selectorsToChange) {
  const newHtml = new DOMParser().parseFromString(container.html, 'text/html');
  const elementForChange = selectorsToChange.split(',');
  elementForChange.forEach(elementSelector => {


    const oldElement = document.querySelector(elementSelector);
    const newElement = newHtml.querySelector(elementSelector);
    console.log(oldElement, newElement);
    if (!oldElement || !newElement) {
      console.warn('ELEMENTS NOT FOUNT' + elementSelector);
      return;
    }
    oldElement.insertAdjacentElement('afterend', newElement);
    oldElement.remove();
  })
}
function changeTextContentOnPageReload(container, selectorsToChange) {
  const newHtml = new DOMParser().parseFromString(container.html, 'text/html');
  const elementForChange = selectorsToChange.split(',');
  elementForChange.forEach(elementSelector => {

    
    const oldElement = document.querySelector(elementSelector);
    const newElement = newHtml.querySelector(elementSelector);
    if (!oldElement || !newElement) {
      console.warn('ELEMENTS NOT FOUNT' + elementSelector);
      return;
    }
    oldElement.textContent = newElement.textContent;
  })
}
function getScriptsFromPage(container) {
    const parser = new DOMParser();
    const dom = parser.parseFromString(container.html, 'text/html');
    const scripts = Array.from(dom.querySelectorAll('[src*="inner-page"]')).map(el => {
        const cloned = el.cloneNode();
        const script = document.createElement('script');
        script.src = el.src + '?v=' + new Date().getTime();
        return script;
        cloned.id = '';
        cloned.src = cloned.src + '?v=' + new Date().getTime();
        cloned.removeAttribute('type');
        return cloned;
    });
    // console.log(scripts);
    return scripts;

}
function getLanguageFromNextContainer(html) {
  return html.match(/lang\=\"([A-Za-z0-9 _]*)\"/)[1];
}

barba.init({
  // requestError: (trigger, action, url, response) => {
  //     // go to a custom 404 page if the user click on a link that return a 404 response status
  //     if (action === 'click' && response.status && response.status === 404) {
  //       barba.go('/404');
  //     }

  //     // prevent Barba from redirecting the user to the requested URL
  //     // this is equivalent to e.preventDefault() in this context
  //     return false;
  //   },
    preventRunning: true,
    debug: true,
    prefetchIgnore: true,
        logLevel: 'error',
    prevent: ({ el }) => el.classList && el.classList.contains('prevent'),
    transitions: [{
        
        leave(el) {
          if (document.querySelector('.js-menu-container.active') !== null) document.querySelector('.js-menu-close').click();
          return gsap.timeline()
            .fromTo('.reloader', { autoAlpha: 0, display:'none' }, { autoAlpha: 1, display: 'flex', duration: 0.5, ease: 'Power4.out' })
            .fromTo('.reloader .loader', { yPercent: 50 }, { yPercent: 0, duration: 0.5, ease: 'Power4.out' }, '<')
            
        },
        afterEnter({ current, next }) {
            const prevScript = document.querySelectorAll('[src*="?inner-page"]');
            const nextContainerLang = getLanguageFromNextContainer(next.html);
            if (document.documentElement.getAttribute('lang') !== nextContainerLang) {
              document.documentElement.setAttribute('lang', nextContainerLang);
              console.log('i here lang');
              changeBlocksOnPageReload(next, '.barba-header-link,.lang-block,.footer-contact,.footer-rights-reserved,.menu-list,.menu-description, .header-right-call, .header-menu .desktop, .menu-close .desktop, .header-left-flat');
              changeTextContentOnPageReload(next, '.form-title');

              
            }
            barba.currentLang = getLanguageFromNextContainer(next.html);

            if (next.namespace === '3d') window.location.reload();

            prevScript.forEach(el => el.remove());

            if (ScrollTrigger) {
                ScrollTrigger.getAll().forEach(el => el.kill());
            }

            // let scriptName = next.container.dataset.barbaNamespace;
            if (next.container.dataset.barbaNamespace === 'home') {
                document.body.setAttribute('id', 'index-page');
                document.body.setAttribute('class', 'index-page');
            } else {
                document.body.setAttribute('id', 'id-page'+next.container.dataset.barbaNamespace);
                document.body.setAttribute('class', 'class-page-'+next.container.dataset.barbaNamespace);
            }
            
            changeBlocksOnPageReload(next, '.header .lang-block');

            const scriptsToLoad = getScriptsFromPage(next);
            window.dispatchEvent(new Event('reloading'));
            let scriptsLoadCount = 0;
            scriptsToLoad.forEach(el => {
                document.body.append(el)
                el.addEventListener('load', function() {
                    scriptsLoadCount += 1;
                    console.log(scriptsLoadCount);
                    if (scriptsLoadCount === scriptsToLoad.length) {
                        
                        document.dispatchEvent(new Event('DOMContentLoaded'));
                        window.dispatchEvent(new Event('DOMContentReloaded'));
                        window.dispatchEvent(new Event('load'));
                        setTimeout(() => {
                          window.dispatchEvent(new Event('preloaderOff'));
                          // console.log('preloaderOff')
                        }, 1000)
                        // console.log('i hefer');
                        return true;
                        return gsap.timeline()
                            .fromTo('.reloader', { autoAlpha: 1 }, { autoAlpha:0, duration: 1, ease: 'Power4.out' })
                            .fromTo('.reloader .loader', { yPercent: 0 }, { yPercent: -50 }, '<')
                    }
                });
                ;
            });
            if (scriptsToLoad.length === 0) {
              
              window.dispatchEvent(new Event('DOMContentReloaded'));
                document.dispatchEvent(new Event('DOMContentLoaded'));
                window.dispatchEvent(new Event('preloaderOff'));
                // console.log('i hefer');
                return true;
                return gsap.timeline()
                    .fromTo('.reloader', { autoAlpha: 1 }, { autoAlpha:0, duration: 1, ease: 'Power4.out' })
                    .fromTo('.reloader .loader', { yPercent: 0 }, { yPercent: -50 }, '<')
            }

            // changeMenu(next);
            const scroll = window.locoScroll;
            // if (scroll) {
            //   window.locoScroll.destroy();
            //   window.locoScroll.init();
            // }
        },
        after(el) {
          return gsap.timeline()
            .fromTo('.reloader', { autoAlpha: 1 }, { autoAlpha:0, duration: 1, ease: 'Power4.out' })
            .fromTo('.reloader .loader', { yPercent: 0 }, { yPercent: -50 }, '<')
        }
      }]
});
var links = document.querySelectorAll('a[href]');
var cbk = function(e) {
 if(e.currentTarget.href === window.location.href) {
   e.preventDefault();
   e.stopPropagation();
 }
};

for(var i = 0; i < links.length; i++) {
  links[i].addEventListener('click', cbk);
}
</script>
