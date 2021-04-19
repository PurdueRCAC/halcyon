
<form method="post" action="{{ route('page', ['uri' => request()->path()]) }}" class="editform">
	<fieldset>
		<legend>About Yourself</legend>

		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label for="apply_name">Name <span class="input-required">*</span></label>
					<input type="text" class="form-control" required name="apply[name]" id="apply_name" />
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label for="apply_email">Email <span class="input-required">*</span></label>
					<input type="email" class="form-control" required name="apply[email]" id="apply_email" />
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label for="apply_institution">Institution <span class="input-required">*</span></label>
					<input type="text" class="form-control" required name="apply[institution]" id="apply_institution" />
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label for="apply_domain">Scientific Domain <span class="input-required">*</span></label>
					<input type="text" class="form-control" required name="apply[domain]" id="apply_domain" />
				</div>
			</div>
		</div>

		<div class="form-group">
			<label for="apply_summary">Short summary of your application</label>
			<textarea class="form-control" name="apply[summary]" data-word-limit="100" cols="45" rows="5" id="apply_summary"></textarea>
			<p class="form-text">Please provide a 50-100 word summary. Word count: <span class="word-count">0</span></p>
		</div>
	</fieldset>

	<fieldset>
		<legend>Please select the primary focus of your needs</legend>

		<div class="form-check">
			<input type="radio" class="form-check-input" name="apply[needs]" id="apply_computational" data-show="#set_computational" data-hide="#set_gateway" value="computational" />
			<label for="apply_computational" class="form-check-label">Computational Needs</label>
			<p class="form-text">Select if you primarily intend to run computational simulations</p>
		</div>

			<div class="form-check">
				<input type="radio" class="form-check-input" name="apply[needs]" id="apply_gateway" data-show="#set_gateway" data-hide="#set_computational" value="gateway" />
				<label for="apply_gateway" class="form-check-label">Science Gateway Needs</label>
				<p class="form-text">Select if you intend to deploy a science gateway or associated services</p>
			</div>
	</fieldset>

	<fieldset id="set_computational" class="form-dependent hide">
		<legend>Computational Needs</legend>

		<div class="form-group">
			<label for="apply_nongateway_software">Software Needs (e.g. compilers, scientific applications, interactive/Windows applications)</label>
			<textarea class="form-control" name="apply[nongateway_software]" cols="45" rows="2" id="apply_nongateway_software"></textarea>
		</div>
		<div class="form-group">
			<label for="apply_nongateway_appropriate">Are there specific computing architectures or systems that are most appropriate (e.g. GPUs, large memory)?</label>
			<textarea class="form-control" name="apply[nongateway_appropriate]" cols="45" rows="2" id="apply_nongateway_appropriate"></textarea>
		</div>
		<div class="form-group">
			<label for="apply_nongateway_scale">To the extent possible, provide an estimate of the scale for your work in terms of core, node, or GPU.</label>
			<textarea class="form-control" name="apply[nongateway_scale]" cols="45" rows="2" id="apply_nongateway_scale"></textarea>
		</div>
		<div class="form-group">
			<label for="apply_nongateway_storage">Describe the storage needs</label>
			<textarea class="form-control" name="apply[nongateway_storage]" cols="45" rows="2" id="apply_nongateway_storage"></textarea>
		</div>

		<fieldset class="form-group">
			<legend>Does your project require access to any public datasets?</legend>

			<div class="form-check">
				<input type="radio" class="form-check-input" name="apply[nongateway_datasets]" id="apply_nongateway_datasets_no" value="no">
				<label for="apply_nongateway_datasets_no" class="form-check-label">No</label>
			</div>

			<div class="form-check">
				<input type="radio" class="form-check-input" name="apply[nongateway_datasets]" id="apply_nongateway_datasets_yes" data-show="#apply_nongateway_datasets_group" value="yes">
				<label for="apply_nongateway_datasets_yes" class="form-check-label">Yes</label>

				<div class="form-group form-dependent hide" id="apply_nongateway_datasets_group">
					<label for="apply_nongateway_datasets">Please describe these datasets.</label>
					<textarea class="form-control" name="apply[nongateway_datasets]" cols="45" rows="2" id="apply_nongateway_datasets"></textarea>
				</div>
			</div>
		</fieldset>
	</fieldset>

	<fieldset id="set_gateway" class="form-dependent hide">
		<legend>Science Gateway Needs</legend>

		<div class="form-group">
			<label for="apply_gateway_services">What services do you want to deploy?</label>
			<textarea class="form-control" name="apply[gateway_services]" cols="45" rows="2" id="apply_gateway_services"></textarea>
		</div>
		<div class="form-group">
			<label for="apply_gateway_storage">What are your storage needs?</label>
			<textarea class="form-control" name="apply[gateway_storage]" cols="45" rows="2" id="apply_gateway_storage"></textarea>
		</div>
		<div class="form-group">
			<label for="apply_gateway_users">How many users do you expect to access your gateway?</label>
			<input type="number" class="form-control" name="apply[gateway_users]" min="0" max="10000" value="1" id="apply_gateway_users" />
		</div>

		<fieldset class="form-group">
			<legend>Do you intend to use any high throughput services (message queue, etc.)?</legend>
			<div class="form-check">
				<input type="radio" class="form-check-input" name="apply[gateway_services]" id="apply_gateway_services_no" value="no">
				<label for="apply_gateway_services_no" class="form-check-label">No</label>
			</div>
			<div class="form-check">
				<input type="radio" class="form-check-input" name="apply[gateway_services]" id="apply_gateway_services_yes" data-show="#apply_gateway_services_group" value="yes">
				<label for="apply_gateway_services_yes" class="form-check-label">Yes</label>

				<div class="form-group form-dependent hide" id="apply_gateway_services_group">
					<label for="apply_gateway_services_details">Please describe the services</label>
					<textarea class="form-control" name="apply[gateway_services_details]" cols="45" rows="2" id="apply_gateway_services_details"></textarea>
				</div>
			</div>
		</fieldset>

		<fieldset class="form-group">
			<legend>Does your gateway also need access to HPC/cloud/GPU?</legend>
			<div class="form-check">
				<input type="radio" class="form-check-input" name="apply[gateway_hpccloudgpu]" id="apply_gateway_hpccloudgpu_no" value="no">
				<label for="apply_gateway_hpccloudgpu_no" class="form-check-label">No</label>
			</div>
			<div class="form-check">
				<input type="radio" class="form-check-input" name="apply[gateway_hpccloudgpu]" id="apply_gateway_hpccloudgpu_yes" data-show="#apply_gateway_hpccloudgpu_group" value="yes">
				<label for="apply_gateway_hpccloudgpu_yes" class="form-check-label">Yes</label>

				<div class="form-group form-dependent hide" id="apply_gateway_hpccloudgpu_group">
					<label for="apply_gateway_hpccloudgpu">Please provide as much detail as possible</label>
					<textarea class="form-control" name="apply[gateway_hpccloudgpu_details]" cols="45" rows="2" id="apply_gateway_hpccloudgpu"></textarea>
				</div>
			</div>
		</fieldset>
	</fieldset>

	<div class="form-group hide d-none">
		<label for="apply_hp">Please leave the following blank</label>
		<input type="text" class="form-control" name="apply[hp]" id="apply_hp" value="" />
	</div>

	@csrf

	<p class="text-center"><input type="submit" id="earlyuser" class="btn btn-primary" btn-api="" value="Submit" /></p>
</form>
