<?php
include('../config.php');
session_start();

// 1. Access Control
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: ../login.php");
    exit;
}
if ($_SESSION["role"] !== "supplier") {  
    header("Location: ../login.php");
    exit;
}

// 2. Handle AJAX CRUD Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $response = ['status' => 'error', 'message' => 'Invalid action'];

    if ($action === 'create') {
        $name = trim($_POST['name']);
        if (!empty($name)) {
            $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->bind_param("s", $name);
            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Category created successfully'];
            }
            $stmt->close();
        }
    } 
    elseif ($action === 'update') {
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        if (!empty($name) && $id > 0) {
            $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
            $stmt->bind_param("si", $name, $id);
            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Category updated successfully'];
            }
            $stmt->close();
        }
    } 
    elseif ($action === 'delete') {
        $id = intval($_POST['id']);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Category deleted successfully'];
            }
            $stmt->close();
        }
    }

    echo json_encode($response);
    exit;
}

// 3. Fetch Categories for Display
$categories = [];
$result = $conn->query("SELECT * FROM categories ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Manage Categories</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
        body { background-color: #f8f9fa; }
        .table-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<?php include('sidebar.php'); ?>

<div class="container py-4">
    <div class="table-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0"><i class="fas fa-list me-2 text-primary"></i>Manage Categories</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="openCreateModal()">
                <i class="fas fa-plus me-1"></i>Create Category
            </button>
        </div>

        <div id="alertPlaceholder"></div>

        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Created At</th>
                        <th class="text-center" style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="categoriesTableBody">
                    <?php if (empty($categories)): ?>
                        <tr><td colspan="4" class="text-center text-muted">No categories found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                            <tr data-id="<?= $category['id'] ?>">
                                <td>#<?= $category['id'] ?></td>
                                <td class="category-name fw-bold"><?= htmlspecialchars($category['name']) ?></td>
                                <td><?= date('M d, Y', strtotime($category['created_at'])) ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-info me-1" onclick="openEditModal(<?= $category['id'] ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteCategory(<?= $category['id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="categoryForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="categoryModalLabel">Create Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="resetForm()"></button>
      </div>
      <div class="modal-body">
          <input type="hidden" id="categoryId" name="id" value="">
          <div class="mb-3">
              <label for="categoryName" class="form-label">Category Name</label>
              <input type="text" class="form-control" id="categoryName" name="name" placeholder="Enter name" required />
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal" onclick="resetForm()">Cancel</button>
        <button type="submit" class="btn btn-primary" id="submitBtn">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));

function showAlert(message, type = 'success') {
    const placeholder = document.getElementById('alertPlaceholder');
    placeholder.innerHTML = `
      <div class="alert alert-${type} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>`;
}

function resetForm() {
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = '';
    document.getElementById('categoryModalLabel').innerText = 'Create Category';
    document.getElementById('submitBtn').innerText = 'Create';
}

function openCreateModal() {
    resetForm();
    categoryModal.show();
}

function openEditModal(id) {
    const row = document.querySelector(`tr[data-id='${id}']`);
    const name = row.querySelector('.category-name').innerText;

    document.getElementById('categoryId').value = id;
    document.getElementById('categoryName').value = name;
    document.getElementById('categoryModalLabel').innerText = 'Edit Category';
    document.getElementById('submitBtn').innerText = 'Update';

    categoryModal.show();
}

function deleteCategory(id) {
    if (!confirm("Are you sure you want to delete this category?")) return;
    
    const params = new URLSearchParams();
    params.append('action', 'delete');
    params.append('id', id);

    fetch(window.location.href, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: params
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            location.reload(); 
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(() => showAlert('Error deleting category', 'danger'));
}

document.getElementById('categoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const id = formData.get('id');
    formData.append('action', id ? 'update' : 'create');

    fetch(window.location.href, {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            location.reload();
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(() => showAlert('Error processing request', 'danger'));
});
</script>
</body>
</html>