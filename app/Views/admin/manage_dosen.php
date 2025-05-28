<?= $this->extend('layout/main_template') ?>
<?= $this->section('content') ?>

<div class="page-heading">
    <h3>Manage Dosen</h3>
</div>

<div class="page-content">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <input type="text" class="form-control w-50" placeholder="Cari Dosen">
                <select class="form-select w-auto ms-2">
                    <option>All status</option>
                    <option>Active</option>
                    <option>Inactive</option>
                </select>
                <a href="#" class="btn btn-primary ms-auto"><i class="bi bi-plus-lg"></i> Tambah Dosen Baru</a>
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>NPM</th>
                        <th>Program Studi</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>John Anderson</td>
                        <td>STD2025001</td>
                        <td>john.a@university.edu</td>
                        <td><span class="badge bg-success">Active</span></td>
                        <td>
                            <a href="#" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-fill"></i></a>
                            <a href="#" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash-fill"></i></a>
                        </td>
                    </tr>
                    <tr>
                        <td>Dr. James Wilson</td>
                        <td>STD2025002</td>
                        <td>sarah.m@university.edu</td>
                        <td><span class="badge bg-success">Active</span></td>
                        <td>
                            <a href="#" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-fill"></i></a>
                            <a href="#" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash-fill"></i></a>
                        </td>
                    </tr>
                    <tr>
                        <td>Dr. James Wilson</td>
                        <td>STD2025003</td>
                        <td>michael.c@university.edu</td>
                        <td><span class="badge bg-success">Active</span></td>
                        <td>
                            <a href="#" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-fill"></i></a>
                            <a href="#" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash-fill"></i></a>
                        </td>
                    </tr>
                </tbody>
            </table>

            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-end">
                    <li class="page-item"><a class="page-link" href="#">1</a></li>
                    <li class="page-item active"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
