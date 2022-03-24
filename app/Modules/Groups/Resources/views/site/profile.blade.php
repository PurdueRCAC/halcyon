<?php
$managedgroups = $user->groups()
	->whereIsManager()
	->get();

if (count($managedgroups)):
	$groups = array();

	foreach ($managedgroups as $groupmembership):
		if (!$groupmembership->group):
			continue;
		endif;
		if ($groupmembership->group->pendingMembersCount > 0):
			$groups[] = $groupmembership->group;
		endif;
	endforeach;

	if (count($groups)):
		?>
		<div class="alert alert-warning">
			<p>
				The following groups have pending membership requests:
			</p>
			<ul>
				<?php foreach ($groups as $group): ?>
					<li>
						<a href="{{ route('site.users.account.section.show.subsection', ['section' => 'groups', 'id' => $group->id, 'subsection' => 'members']) }}">{{ $group->name }}</a> <span class="badge badge-warning">{{ $group->pendingMembersCount }}</span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	endif;
endif;
