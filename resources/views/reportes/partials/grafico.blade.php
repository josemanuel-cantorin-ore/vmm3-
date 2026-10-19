<section class="card h-100 report-chart-card">
    <div class="card-header bg-white">
        <h2 class="h6 card-title mb-1">{{ $titulo }}</h2>
        <p class="small text-muted mb-0">{{ $descripcion }}</p>
    </div>
    <div class="card-body">
        <div class="report-chart-wrap">
            <canvas id="{{ $id }}" role="img" aria-label="{{ $titulo }}"></canvas>
        </div>
        <p class="report-chart-empty text-muted small text-center d-none mb-0" data-empty-for="{{ $id }}">
            Aún no hay datos para mostrar en este periodo.
        </p>
    </div>
</section>
