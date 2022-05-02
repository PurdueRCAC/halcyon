@if (Session::has('success'))
    <div class="alert alert-success">
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
    <div class="alert alert-danger">
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
    <div class="alert alert-warning">
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
