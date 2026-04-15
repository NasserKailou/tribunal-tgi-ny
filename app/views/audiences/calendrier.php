<?php $pageTitle = 'Calendrier des audiences'; ?>
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-calendar3 me-2 text-primary"></i>Calendrier des audiences</h4>
    <a href="<?=BASE_URL?>/audiences/create" class="btn btn-primary btn-sm"><i class="bi bi-plus me-1"></i>Planifier</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div id="audienceCalendar"></div>
    </div>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/fr.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded',function(){
    const cal=new FullCalendar.Calendar(document.getElementById('audienceCalendar'),{
        initialView:'dayGridMonth',locale:'fr',
        events:function(fetchInfo,success,failure){
            fetch(`<?=BASE_URL?>/api/audiences-calendrier?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`).then(r=>r.json()).then(success).catch(failure);
        },
        headerToolbar:{left:'prev,next today',center:'title',right:'dayGridMonth,timeGridWeek,listMonth'},
        eventClick:function(info){window.location.href=info.event.url;info.jsEvent.preventDefault();},
        height:'auto'
    });
    cal.render();
});
</script>
