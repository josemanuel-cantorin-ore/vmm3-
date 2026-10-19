document.addEventListener('DOMContentLoaded', function() {
    // Inicializar iconos de Feather
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    const aulaLinks = document.querySelectorAll('.aula-link');
    
    aulaLinks.forEach(link => {
        const card = link.querySelector('.aula-card');
        if (!card) return;

        // EFECTO 1: Onda (Ripple) al hacer clic
        link.addEventListener('click', function(e) {
            const rect = card.getBoundingClientRect();
            
            const ripple = document.createElement('span');
            ripple.classList.add('ripple');
            
            const size = 100;
            ripple.style.width = `${size}px`;
            ripple.style.height = `${size}px`;
            ripple.style.left = `${e.clientX - rect.left - size / 2}px`;
            ripple.style.top = `${e.clientY - rect.top - size / 2}px`;
            
            card.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });

        // EFECTO 2: Inclinación 3D al mover el mouse
        link.addEventListener('mousemove', function(e) {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const rotateX = ((y - centerY) / centerY) * -3;
            const rotateY = ((x - centerX) / centerX) * 3;
            
            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.05) translateY(-10px)`;
        });

        // EFECTO 3: Resetear al salir
        link.addEventListener('mouseleave', function() {
            card.style.transform = '';
        });
    });
});