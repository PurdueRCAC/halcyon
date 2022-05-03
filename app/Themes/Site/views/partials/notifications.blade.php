@if (Session::has('success'))
    <div class="alert alert-success fade in alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <?php
        $err = Session::get('success');

        if (is_array($err)):
            foreach ($err as $i => $er):
                $err[$i] = e($er);
                echo implode('<br />', $err);
            endforeach;
        else:
            echo e($err);
        endif;
        ?>
    </div>
@endif

@if (Session::has('error'))
    <div class="alert alert-danger fade in alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <?php
        $err = Session::get('error');

        if (is_array($err)):
            foreach ($err as $i => $er):
                $err[$i] = e($er);
                echo implode('<br />', $err);
            endforeach;
        else:
            echo e($err);
        endif;
        ?>
    </div>
@endif

@if (Session::has('warning'))
    <div class="alert alert-warning fade in alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <?php
        $err = Session::get('warning');

        if (is_array($err)):
            foreach ($err as $i => $er):
                $err[$i] = e($er);
                echo implode('<br />', $err);
            endforeach;
        else:
            echo e($err);
        endif;
        ?>
    </div>
@endif
