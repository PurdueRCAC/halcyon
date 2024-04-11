<div id="system-messages">
@foreach (['success', 'error', 'danger', 'warning', 'info'] as $type)
    @if (\Illuminate\Support\Facades\Session::has($type))
        <div class="alert alert-{{ $type }} fade in alert-dismissable" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <?php
            $err = \Illuminate\Support\Facades\Session::get($type);

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
@endforeach
</div>