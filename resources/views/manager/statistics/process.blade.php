<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?=url('manager')?>">Inicio</a></li>
        <li class="breadcrumb-item"><a href="<?=url('manager/estadisticas')?>">Estadisticas</a></li>
        <li class="breadcrumb-item"><a href="<?=url('manager/estadisticas/cuentas')?>">Cuentas</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?=$title?></li>
    </ol>
</nav>

<p style="text-align: right; color: red;">*Estadisticas con respecto a los últimos 30 días.</p>

<table class="table table-striped">
    <thead>
    <tr>
        <th>Proceso</th>
        <th>Nº de Trámites</th>
      
    </tr>
    </thead>
    <tbody>
    <?php foreach($procesos as $p): ?>
    <tr>
        <td><a href="<?=url('manager/estadisticas/cuentas/'.$p->cuenta_id.'/'.$p->id)?>"><?=$p->nombre?></a></td>
        <td><?=$p->ntramites?></td>
       
    </tr>
    <?php endforeach; ?>

    <tr class="table-success">
        <td>Total</td>
        <td><?=$ntramites?></td>
    </tr>
    </tbody>
</table>