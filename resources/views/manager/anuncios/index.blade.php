<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page"><?=$title?></li>
    </ol>
</nav>

<p><a class="btn btn-primary" href="<?=url('manager/anuncios/editar')?>">Crear Anuncio</a></p>

<table class="table">
    <thead>
    <tr>
        <th>Texto</th>
        <th class="text-center">Tipo</th>
        <th>Acciones</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($anuncios as $c):?>
    <tr>
        <td><?=$c->texto?></td>
        <td><?=$c->tipo?></td>
        <!-- <td class="text-center"><span class="badge badge-secondary"><?=strtoupper($c->ambiente)?></span></td> -->
        <td>
            <a class="btn btn-primary" href="<?=url('manager/anuncios/editar/' . $c->id)?>">
                <i class="material-icons">edit</i> Editar
            </a>
            <a class="btn btn-success" href="<?=url('manager/anuncios/activar/' . $c->id)?>"
               onclick="return confirm('¿Está seguro que desea activar este anuncio?')">
                <i class="material-icons">done</i> Activar
            </a>
            <a class="btn btn-danger" href="<?=url('manager/anuncios/eliminar/' . $c->id)?>"
               onclick="return confirm('¿Está seguro que desea eliminar este anuncio?')">
                <i class="material-icons">delete</i> Eliminar
            </a>
        </td>
    </tr>
    <?php endforeach ?>
    </tbody>
</table>