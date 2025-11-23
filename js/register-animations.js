(function(){

  function wrapWords(el){

    const txt = el.textContent;

    el.textContent = '';

    const frag = document.createDocumentFragment();

    const parts = txt.split(/(\s+)/); 

    parts.forEach((p, i) => {

      if (p.trim().length === 0) {

        frag.appendChild(document.createTextNode(p)); 

      } else {

        const span = document.createElement('span');

        span.className = 'word';

        span.textContent = p;

        span.style.transitionDelay = '0s';

        frag.appendChild(span);

      }

    });

    el.appendChild(frag);

  }



  const blurNodes = Array.from(document.querySelectorAll('.blur-words'));

  blurNodes.forEach(wrapWords);



  const wordIO = new IntersectionObserver((entries) => {

    entries.forEach(entry => {

      if (!entry.isIntersecting) return;

      const el = entry.target;

      const delayBase = parseFloat(getComputedStyle(el).getPropertyValue('--wd')) || 0.12; // seconds

      const words = el.querySelectorAll('.word');

      words.forEach((w, idx) => {

        w.style.transitionDelay = (idx * delayBase) + 's';

        requestAnimationFrame(() => w.classList.add('in'));

      });

      wordIO.unobserve(el);

    });

  }, { threshold: 0.3 });



  blurNodes.forEach(n => wordIO.observe(n));





  const revIO = new IntersectionObserver((entries)=>{

    entries.forEach(e=>{

      if (e.isIntersecting) {

        e.target.classList.add('in');

        revIO.unobserve(e.target);

      }

    });

  }, { threshold: 0.12 });



  document.querySelectorAll('.reveal').forEach(el => revIO.observe(el));

})();

