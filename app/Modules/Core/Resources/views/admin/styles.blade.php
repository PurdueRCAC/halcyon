@extends('layouts.master')

@push('styles')
<style>
	.flex-list-wrapper,
	.flex-table,
	.flex-list-inner {
		width: 100%;
	}
	.flex-table .flex-table-header {
display: -webkit-box;
display: -ms-flexbox;
display: flex;
-webkit-box-align: center;
-ms-flex-align: center;
align-items: center;
padding: 0 10px;
}
.flex-table .flex-table-header span {
-webkit-box-flex: 1;
-ms-flex: 1 1 0px;
flex: 1 1 0px;
display: -webkit-box;
display: -ms-flexbox;
display: flex;
-webkit-box-align: center;
-ms-flex-align: center;
align-items: center;
font-size: .8rem;
font-weight: 600;
color: #999;
text-transform: uppercase;
padding: 0 10px 10px 10px;
}
.flex-table .flex-table-header span.cell-end {
-webkit-box-pack: end;
-ms-flex-pack: end;
justify-content: flex-end;
}
.flex-table .flex-table-header span.is-grow {
-webkit-box-flex: 2;
-ms-flex-positive: 2;
flex-grow: 2;
}
.flex-table .flex-table-item {
display: -webkit-box;
display: -ms-flexbox;
display: flex;
-webkit-box-align: stretch;
-ms-flex-align: stretch;
align-items: stretch;
width: 100%;

background: rgba(255, 255, 255, 0.05);
border-radius: 0.25em;
border: none;
padding: 0.75rem;
margin-bottom: 1px;
}
.flex-table .flex-table-item .flex-table-cell {
	-webkit-box-flex: 1;
	-ms-flex: 1 1 0px;
	flex: 1 1 0px;
	display: -webkit-box;
	display: -ms-flexbox;
	display: flex;
	-webkit-box-align: center;
	-ms-flex-align: center;
	align-items: center;
	padding: 0 0.75rem;
	font-family: "Roboto",sans-serif;
}
.flex-table .flex-table-item .flex-table-cell.cell-end {
-webkit-box-pack: end;
-ms-flex-pack: end;
justify-content: flex-end;
}
.flex-table .flex-table-item .flex-table-cell.is-grow {
-webkit-box-flex: 2;
-ms-flex-positive: 2;
flex-grow: 2;
}
.flex-table .flex-table-item .flex-table-cell.is-user,
.flex-table .flex-table-item .flex-table-cell.is-media {
padding-left: 0;
}
.flex-table .flex-table-item .flex-table-cell.is-user.is-grow,
.flex-table .flex-table-item .flex-table-cell.is-media.is-grow {
-webkit-box-flex: 2;
-ms-flex-positive: 2;
flex-grow: 2;
}
</style>
@endpush

@section('title')
Style guide
@stop

@section('content')
	<p>Etiam porta sem malesuada magna mollis euismod. Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Donec sed odio dui. Nulla vitae elit libero, a pharetra augue. Donec sed odio dui. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit.</p>

	<p class="text-muted">Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Donec sed odio dui. Vestibulum id ligula porta felis euismod semper. Nulla vitae elit libero, a pharetra augue. Nullam id dolor id nibh ultricies vehicula ut id elit.</p>

	<h2>Badges</h2>
	<div>
		<span class="badge badge-primary">Primary</span>
		<span class="badge badge-secondary">Secondary</span>
		<span class="badge badge-success">Success</span>
		<span class="badge badge-danger">Danger</span>
		<span class="badge badge-warning">Warning</span>
		<span class="badge badge-info">Info</span>
		<span class="badge badge-light">Light</span>
		<span class="badge badge-dark">Dark</span>
	</div>

	<h2>Buttons</h2>
	<div>
		<button type="button" class="btn btn-primary">Primary</button>
		<button type="button" class="btn btn-secondary">Secondary</button>
		<button type="button" class="btn btn-success">Success</button>
		<button type="button" class="btn btn-danger">Danger</button>
		<button type="button" class="btn btn-warning">Warning</button>
		<button type="button" class="btn btn-info">Info</button>
		<button type="button" class="btn btn-light">Light</button>
		<button type="button" class="btn btn-dark">Dark</button>
		<button type="button" class="btn btn-link">Link</button>
	</div>

	<h3>Active</h3>
	<div>
		<button type="button" class="btn btn-primary active" aria-pressed="true">Primary</button>
		<button type="button" class="btn btn-secondary active" aria-pressed="true">Secondary</button>
		<button type="button" class="btn btn-success active" aria-pressed="true">Success</button>
		<button type="button" class="btn btn-danger active" aria-pressed="true">Danger</button>
		<button type="button" class="btn btn-warning active" aria-pressed="true">Warning</button>
		<button type="button" class="btn btn-info active" aria-pressed="true">Info</button>
		<button type="button" class="btn btn-light active" aria-pressed="true">Light</button>
		<button type="button" class="btn btn-dark active" aria-pressed="true">Dark</button>
		<button type="button" class="btn btn-link active" aria-pressed="true">Link</button>
	</div>

	<h3>Disabled</h3>
	<div>
		<button type="button" class="btn btn-primary" disabled>Primary</button>
		<button type="button" class="btn btn-secondary" disabled>Secondary</button>
		<button type="button" class="btn btn-success" disabled>Success</button>
		<button type="button" class="btn btn-danger" disabled>Danger</button>
		<button type="button" class="btn btn-warning" disabled>Warning</button>
		<button type="button" class="btn btn-info" disabled>Info</button>
		<button type="button" class="btn btn-light" disabled>Light</button>
		<button type="button" class="btn btn-dark" disabled>Dark</button>
		<button type="button" class="btn btn-link" disabled>Link</button>
	</div>

	<div class="dropdown">
  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    Dropdown button
  </button>
  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
    <a class="dropdown-item" href="#">Action</a>
    <a class="dropdown-item" href="#">Another action</a>
    <a class="dropdown-item" href="#">Something else here</a>
  </div>
</div>

<h2>Alerts</h2>
<div class="alert alert-primary" role="alert">
  A simple primary alert—check it out!
</div>
<div class="alert alert-secondary" role="alert">
  A simple secondary alert—check it out!
</div>
<div class="alert alert-success" role="alert">
  A simple success alert—check it out!
</div>
<div class="alert alert-danger" role="alert">
  A simple danger alert—check it out!
</div>
<div class="alert alert-warning" role="alert">
  A simple warning alert—check it out!
</div>
<div class="alert alert-info" role="alert">
  A simple info alert—check it out!
</div>
<div class="alert alert-light" role="alert">
  A simple light alert—check it out!
</div>
<div class="alert alert-dark" role="alert">
  A simple dark alert—check it out!
</div>

<div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
  <div class="toast-header">
    <img src="..." class="rounded mr-2" alt="...">
    <strong class="mr-auto">Bootstrap</strong>
    <small>11 mins ago</small>
    <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
      <span class="visually-hidden" aria-hidden="true">&times;</span>
    </button>
  </div>
  <div class="toast-body">
    Hello, world! This is a toast message.
  </div>
</div>
<h2>Tables</h2>
<table class="table">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">First</th>
      <th scope="col">Last</th>
      <th scope="col">Handle</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">1</th>
      <td>Mark</td>
      <td>Otto</td>
      <td>@mdo</td>
    </tr>
    <tr>
      <th scope="row">2</th>
      <td>Jacob</td>
      <td>Thornton</td>
      <td>@fat</td>
    </tr>
    <tr>
      <th scope="row">3</th>
      <td>Larry</td>
      <td>the Bird</td>
      <td>@twitter</td>
    </tr>
  </tbody>
</table>
<table class="table table-striped">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">First</th>
      <th scope="col">Last</th>
      <th scope="col">Handle</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">1</th>
      <td>Mark</td>
      <td>Otto</td>
      <td>@mdo</td>
    </tr>
    <tr>
      <th scope="row">2</th>
      <td>Jacob</td>
      <td>Thornton</td>
      <td>@fat</td>
    </tr>
    <tr>
      <th scope="row">3</th>
      <td>Larry</td>
      <td>the Bird</td>
      <td>@twitter</td>
    </tr>
  </tbody>
</table>
<table class="table table-bordered">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">First</th>
      <th scope="col">Last</th>
      <th scope="col">Handle</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">1</th>
      <td>Mark</td>
      <td>Otto</td>
      <td>@mdo</td>
    </tr>
    <tr>
      <th scope="row">2</th>
      <td>Jacob</td>
      <td>Thornton</td>
      <td>@fat</td>
    </tr>
    <tr>
      <th scope="row">3</th>
      <td>Larry</td>
      <td>the Bird</td>
      <td>@twitter</td>
    </tr>
  </tbody>
</table>
<table class="table table-borderless">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">First</th>
      <th scope="col">Last</th>
      <th scope="col">Handle</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">1</th>
      <td>Mark</td>
      <td>Otto</td>
      <td>@mdo</td>
    </tr>
    <tr>
      <th scope="row">2</th>
      <td>Jacob</td>
      <td>Thornton</td>
      <td>@fat</td>
    </tr>
    <tr>
      <th scope="row">3</th>
      <td>Larry</td>
      <td>the Bird</td>
      <td>@twitter</td>
    </tr>
  </tbody>
</table>

<div class="flex-list-wrapper flex-list-v1 mb-4">
		<div class="flex-table">

			<!--Table header-->
			<div class="flex-table-header" data-filter-hide="">
				<span class="is-grow">User</span>
				<span>Location</span>
				<span>Industry</span>
				<span>Status</span>
				<span>Relations</span>
				<span class="cell-end">Actions</span>
			</div>

			<div class="flex-list-inner">
				<!--Table item-->
				<div class="table-row">
				<div class="flex-table-item">
					<div class="flex-table-cell is-media is-grow">
						<div class="h-avatar is-medium">
							<img class="avatar" src="assets/img/avatars/photos/8.jpg" data-demo-src="assets/img/avatars/photos/8.jpg" alt="" data-user-popover="3">
							<img class="badge" src="assets/img/icons/flags/united-states-of-america.svg" data-demo-src="assets/img/icons/flags/united-states-of-america.svg" alt="">
						</div>
						<div>
							<span class="item-name dark-inverted" data-filter-match="">Erik K.</span>
							<span class="item-meta">
									<span data-filter-match="">Product Manager</span>
							</span>
						</div>
					</div>
					<div class="flex-table-cell" data-th="Location">
						<span class="light-text" data-filter-match="">New York, NY</span>
					</div>
					<div class="flex-table-cell" data-th="Industry">
						<span class="light-text" data-filter-match="">Software</span>
					</div>
					<div class="flex-table-cell" data-th="Status">
						<span class="tag is-success is-rounded" data-filter-match="">Online</span>
					</div>
					<div class="flex-table-cell" data-th="Relations">
						<div class="avatar-stack is-pushed-mobile">
							<div class="h-avatar is-small">
								<span class="avatar is-fake is-info" data-user-popover="34">
									<span>JD</span>
								</span>
							</div>
						</div>
					</div>
					<div class="flex-table-cell cell-end" data-th="Actions">
						<div class="dropdown is-spaced is-dots is-right dropdown-trigger is-pushed-mobile">
							<div class="is-trigger" aria-haspopup="true">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-vertical"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
							</div>
							<div class="dropdown-menu" role="menu">
								<div class="dropdown-content">
									<a href="#" class="dropdown-item is-media">
										<div class="icon">
											<span class="lnil lnil-eye"></span>
										</div>
										<div class="meta">
											<span>View</span>
											<span>View user details</span>
										</div>
									</a>
									<a href="#" class="dropdown-item is-media">
										<div class="icon">
											<span class="lnil lnil-briefcase"></span>
										</div>
										<div class="meta">
											<span>Projects</span>
											<span>View user projects</span>
										</div>
									</a>
									<a href="#" class="dropdown-item is-media">
										<div class="icon">
											<span class="lnil lnil-calendar"></span>
										</div>
										<div class="meta">
											<span>Schedule</span>
											<span>Schedule a meeting</span>
										</div>
									</a>
									<hr class="dropdown-divider">
									<a href="#" class="dropdown-item is-media">
										<div class="icon">
											<span class="lnil lnil-trash-can-alt"></span>
										</div>
										<div class="meta">
											<span>Remove</span>
											<span>Remove from list</span>
										</div>
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="table-row d-none">
					Detailed info here
				</div>
				</div>

				<!--Table item-->
				<div class="flex-table-item">
					<div class="flex-table-cell is-media is-grow">
						<div class="h-avatar is-medium">
							<img class="avatar" src="assets/img/avatars/photos/22.jpg" data-demo-src="assets/img/avatars/photos/22.jpg" alt="" data-user-popover="5">
							<img class="badge" src="assets/img/icons/flags/united-states-of-america.svg" data-demo-src="assets/img/icons/flags/united-states-of-america.svg" alt="">
						</div>
						<div>
							<span class="item-name dark-inverted" data-filter-match="">Jimmy H.</span>
							<span class="item-meta">
									<span data-filter-match="">Project Manager</span>
							</span>
						</div>
					</div>
					<div class="flex-table-cell" data-th="Location">
						<span class="light-text" data-filter-match="">Los Angeles, CA</span>
					</div>
					<div class="flex-table-cell" data-th="Industry">
						<span class="light-text" data-filter-match="">Business</span>
					</div>
					<div class="flex-table-cell" data-th="Status">
						<span class="tag is-rounded" data-filter-match="">Offline</span>
					</div>
					<div class="flex-table-cell" data-th="Relations">
						<div class="avatar-stack is-pushed-mobile">
							<div class="h-avatar is-small">
								<span class="avatar is-fake is-danger" data-user-popover="35">
										<span>SC</span>
								</span>
							</div>
						</div>
					</div>
					<div class="flex-table-cell cell-end" data-th="Actions">
						<div class="dropdown is-spaced is-dots is-right dropdown-trigger is-pushed-mobile">
							<div class="is-trigger" aria-haspopup="true">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-vertical"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
							</div>
							<div class="dropdown-menu" role="menu">
								<div class="dropdown-content">
									<a href="#" class="dropdown-item is-media">
										<div class="icon">
											<span class="lnil lnil-eye"></span>
										</div>
										<div class="meta">
											<span>View</span>
											<span>View user details</span>
										</div>
									</a>
									<a href="#" class="dropdown-item is-media">
										<div class="icon">
											<span class="lnil lnil-briefcase"></span>
										</div>
										<div class="meta">
											<span>Projects</span>
											<span>View user projects</span>
										</div>
									</a>
									<a href="#" class="dropdown-item is-media">
										<div class="icon">
											<span class="lnil lnil-calendar"></span>
										</div>
										<div class="meta">
											<span>Schedule</span>
											<span>Schedule a meeting</span>
										</div>
									</a>
									<hr class="dropdown-divider">
									<a href="#" class="dropdown-item is-media">
										<div class="icon">
											<span class="lnil lnil-trash-can-alt"></span>
										</div>
										<div class="meta">
											<span>Remove</span>
											<span>Remove from list</span>
										</div>
									</a>
								</div>
							</div>
						</div>
					</div>
				</div><!-- / .flex-table-item -->

			</div><!-- / .flex-list-inner -->
		</div><!-- / .flex-table -->
	</div>

	<h2>Forms</h2>
	<form>
  <div class="form-group">
    <label for="exampleFormControlInput1">Email address</label>
    <input type="email" class="form-control" id="exampleFormControlInput1" placeholder="name@example.com">
  </div>
  <div class="form-group form-check">
    <input type="checkbox" class="form-check-input" id="exampleCheck1">
    <label class="form-check-label" for="exampleCheck1">Check me out</label>
  </div>
  <div class="form-check">
  <input class="form-check-input" type="checkbox" value="" id="defaultCheck2" disabled>
  <label class="form-check-label" for="defaultCheck2">
    Disabled checkbox
  </label>
</div>
<div class="form-check">
  <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios1" value="option1" checked>
  <label class="form-check-label" for="exampleRadios1">
    Default radio
  </label>
</div>
<div class="form-check">
  <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios2" value="option2">
  <label class="form-check-label" for="exampleRadios2">
    Second default radio
  </label>
</div>
<div class="form-check">
  <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios3" value="option3" disabled>
  <label class="form-check-label" for="exampleRadios3">
    Disabled radio
  </label>
</div>
  <div class="form-group">
    <label for="exampleFormControlSelect1">Example select</label>
    <select class="form-control" id="exampleFormControlSelect1">
      <option>1</option>
      <option>2</option>
      <option>3</option>
      <option>4</option>
      <option>5</option>
    </select>
  </div>
  <div class="form-group">
    <label for="exampleFormControlSelect2">Example multiple select</label>
    <select multiple class="form-control" id="exampleFormControlSelect2">
      <option>1</option>
      <option>2</option>
      <option>3</option>
      <option>4</option>
      <option>5</option>
    </select>
  </div>
  <div class="form-group">
    <label for="formControlRange">Example Range input</label>
    <input type="range" class="form-control-range" id="formControlRange">
  </div>
  <div class="form-group">
  <label for="exampleFormControlReadonly">Read only</label>
  <input class="form-control" type="text" id="exampleFormControlReadonly" placeholder="Readonly input here..." readonly>
</div>
  <div class="form-group">
    <label for="exampleFormControlTextarea1">Example textarea</label>
    <textarea class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>
  </div>
  <div class="form-group row">
    <label for="staticEmail" class="col-sm-2 col-form-label">Email</label>
    <div class="col-sm-10">
      <input type="text" readonly class="form-control-plaintext" id="staticEmail" value="email@example.com">
    </div>
  </div>
  <div class="form-group row">
    <label for="inputPassword" class="col-sm-2 col-form-label">Password</label>
    <div class="col-sm-10">
      <input type="password" class="form-control" id="inputPassword">
    </div>
  </div>
</form>

<form>
  <div class="form-row">
    <div class="col-md-6 mb-3">
      <label for="validationServer01">First name</label>
      <input type="text" class="form-control is-valid" id="validationServer01" value="Mark" required>
      <div class="valid-feedback">
        Looks good!
      </div>
    </div>
    <div class="col-md-6 mb-3">
      <label for="validationServer02">Last name</label>
      <input type="text" class="form-control is-valid" id="validationServer02" value="Otto" required>
      <div class="valid-feedback">
        Looks good!
      </div>
    </div>
  </div>
  <div class="form-row">
    <div class="col-md-6 mb-3">
      <label for="validationServer03">City</label>
      <input type="text" class="form-control is-invalid" id="validationServer03" aria-describedby="validationServer03Feedback" required>
      <div id="validationServer03Feedback" class="invalid-feedback">
        Please provide a valid city.
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <label for="validationServer04">State</label>
      <select class="custom-select is-invalid" id="validationServer04" aria-describedby="validationServer04Feedback" required>
        <option selected disabled value="">Choose...</option>
        <option>...</option>
      </select>
      <div id="validationServer04Feedback" class="invalid-feedback">
        Please select a valid state.
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <label for="validationServer05">Zip</label>
      <input type="text" class="form-control is-invalid" id="validationServer05" aria-describedby="validationServer05Feedback" required>
      <div id="validationServer05Feedback" class="invalid-feedback">
        Please provide a valid zip.
      </div>
    </div>
  </div>
  <div class="form-group">
    <div class="form-check">
      <input class="form-check-input is-invalid" type="checkbox" value="" id="invalidCheck3" aria-describedby="invalidCheck3Feedback" required>
      <label class="form-check-label" for="invalidCheck3">
        Agree to terms and conditions
      </label>
      <div  id="invalidCheck3Feedback" class="invalid-feedback">
        You must agree before submitting.
      </div>
    </div>
  </div>
  <button class="btn btn-primary" type="submit">Submit form</button>
</form>

	<h2>Cards</h2>
	<div class="card" style="width: 18rem;">
	<div class="card-img-top">...</div>
	<div class="card-body">
		<h5 class="card-title">Card title</h5>
		<h6 class="card-subtitle mb-2 text-muted">Card subtitle</h6>
		<p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
		<a href="#" class="btn btn-primary">Go somewhere</a>
	</div>
	</div>
	<br />
	<div class="card">
  <div class="card-header">
    Featured
  </div>
  <div class="card-body">
    <h5 class="card-title">Special title treatment</h5>
    <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
    <a href="#" class="btn btn-primary">Go somewhere</a>
  </div>
</div>
<br />
<div class="card text-center">
  <div class="card-header">
    Featured
  </div>
  <div class="card-body">
    <h5 class="card-title">Special title treatment</h5>
    <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
    <a href="#" class="btn btn-primary">Go somewhere</a>
  </div>
  <div class="card-footer text-muted">
    2 days ago
  </div>
</div>
<br />
<div class="card text-center">
  <div class="card-header">
    <ul class="nav nav-tabs card-header-tabs">
      <li class="nav-item">
        <a class="nav-link active" href="#">Active</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Link</a>
      </li>
      <li class="nav-item">
        <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Disabled</a>
      </li>
    </ul>
  </div>
  <div class="card-body">
    <h5 class="card-title">Special title treatment</h5>
    <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
    <a href="#" class="btn btn-primary">Go somewhere</a>
  </div>
</div>
<br />
<div class="card text-center">
  <div class="card-header">
    <ul class="nav nav-pills card-header-pills">
      <li class="nav-item">
        <a class="nav-link active" href="#">Active</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Link</a>
      </li>
      <li class="nav-item">
        <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Disabled</a>
      </li>
    </ul>
  </div>
  <div class="card-body">
    <h5 class="card-title">Special title treatment</h5>
    <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
    <a href="#" class="btn btn-primary">Go somewhere</a>
  </div>
</div>
<br />
	<h3>List groups</h3>
	<div class="card" style="width: 18rem;">
	<ul class="list-group list-group-flush">
		<li class="list-group-item">Cras justo odio</li>
		<li class="list-group-item">Dapibus ac facilisis in</li>
		<li class="list-group-item">Vestibulum at eros</li>
	</ul>
	</div>
	<br />
	<div class="card text-white bg-primary mb-3" style="max-width: 18rem;">
  <div class="card-header">Header</div>
  <div class="card-body">
    <h5 class="card-title">Primary card title</h5>
    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
  </div>
</div>
<div class="card text-white bg-secondary mb-3" style="max-width: 18rem;">
  <div class="card-header">Header</div>
  <div class="card-body">
    <h5 class="card-title">Secondary card title</h5>
    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
  </div>
</div>
<div class="card text-white bg-success mb-3" style="max-width: 18rem;">
  <div class="card-header">Header</div>
  <div class="card-body">
    <h5 class="card-title">Success card title</h5>
    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
  </div>
</div>
<div class="card text-white bg-danger mb-3" style="max-width: 18rem;">
  <div class="card-header">Header</div>
  <div class="card-body">
    <h5 class="card-title">Danger card title</h5>
    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
  </div>
</div>
<div class="card text-white bg-warning mb-3" style="max-width: 18rem;">
  <div class="card-header">Header</div>
  <div class="card-body">
    <h5 class="card-title">Warning card title</h5>
    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
  </div>
</div>
<div class="card text-white bg-info mb-3" style="max-width: 18rem;">
  <div class="card-header">Header</div>
  <div class="card-body">
    <h5 class="card-title">Info card title</h5>
    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
  </div>
</div>
<div class="card bg-light mb-3" style="max-width: 18rem;">
  <div class="card-header">Header</div>
  <div class="card-body">
    <h5 class="card-title">Light card title</h5>
    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
  </div>
</div>
<div class="card text-white bg-dark mb-3" style="max-width: 18rem;">
  <div class="card-header">Header</div>
  <div class="card-body">
    <h5 class="card-title">Dark card title</h5>
    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
  </div>
</div>
<div class="card-deck">
  <div class="card">
    <img src="..." class="card-img-top" alt="...">
    <div class="card-body">
      <h5 class="card-title">Card title</h5>
      <p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
    </div>
    <div class="card-footer">
      <small class="text-muted">Last updated 3 mins ago</small>
    </div>
  </div>
  <div class="card">
    <img src="..." class="card-img-top" alt="...">
    <div class="card-body">
      <h5 class="card-title">Card title</h5>
      <p class="card-text">This card has supporting text below as a natural lead-in to additional content.</p>
    </div>
    <div class="card-footer">
      <small class="text-muted">Last updated 3 mins ago</small>
    </div>
  </div>
  <div class="card">
    <img src="..." class="card-img-top" alt="...">
    <div class="card-body">
      <h5 class="card-title">Card title</h5>
      <p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This card has even longer content than the first to show that equal height action.</p>
    </div>
    <div class="card-footer">
      <small class="text-muted">Last updated 3 mins ago</small>
    </div>
  </div>
</div>
@stop