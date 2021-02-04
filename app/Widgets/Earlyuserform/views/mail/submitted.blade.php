**Institution**
> {{ $data['institution'] }}

**Scientific Domain**
> {{ $data['domain'] }}


### Non-Gateway User Needs

**Software Needs (e.g. compilers, scientific applications, interactive/Windows applications)**
> {{ !empty($data['nongateway_software']) ? $data['nongateway_software'] : trans('global.none') }}

**Are there specific computing architectures or systems that are most appropriate (e.g. GPUs, large memory)?**
> {{ !empty($data['nongateway_appropriate']) ? $data['nongateway_appropriate'] : trans('global.none') }}

**To the extent possible, provide an estimate of the scale for your work in terms of core, node, or GPU.**
> {{ !empty($data['nongateway_scale']) ? $data['nongateway_scale'] : trans('global.none') }}

**Describe the storage needs**
> {{ !empty($data['nongateway_storage']) ? $data['nongateway_storage'] : trans('global.none') }}

**Does your project require access to any public datasets?**
> {{ !empty($data['nongateway_datasets']) ? $data['nongateway_datasets'] : trans('global.none') }}

@if (!empty($data['nongateway_datasets']) && $data['nongateway_datasets'] == 'yes')
**Please describe these datasets.**
> {{ !empty($data['nongateway_datasets_details']) ? $data['nongateway_datasets_details'] : trans('global.none') }}
@endif


### Gateway User Needs

**What services do you want to deploy?**
> {{ !empty($data['gateway_services']) ? $data['gateway_services'] : trans('global.none') }}

**What are your storage needs?**
> {{ !empty($data['gateway_storage']) ? $data['gateway_storage'] : trans('global.none') }}

**How many users do you want to support?**
> {{ !empty($data['gateway_users']) ? $data['gateway_users'] : 0 }}

**Do you intend to use any high throughput services (message queue, etc.)?**
> {{ !empty($data['gateway_services']) ? $data['gateway_services'] : trans('global.none') }}

@if (!empty($data['gateway_services']) && $data['gateway_services'] == 'yes')
**Please describe the services**
> {{ !empty($data['gateway_services_details']) ? $data['gateway_services_details'] : trans('global.none') }}
@endif

**Do you also need access to HPC/cloud/GPU?**
> {{ !empty($data['gateway_hpccloudgpu']) ? $data['gateway_hpccloudgpu'] : trans('global.none') }}

@if (!empty($data['gateway_hpccloudgpu']) && $data['gateway_hpccloudgpu'] == 'yes')
**Please provide as much detail as possible**
> {{ !empty($data['gateway_hpccloudgpu_details']) ? $data['gateway_hpccloudgpu_details'] : trans('global.none') }}
@endif
