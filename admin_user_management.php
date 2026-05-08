<?php
$pageTitle  = 'User Management';
$sidebarKey = 'admin_users';
require_once 'helper_layout_admin.php';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id !== (int)$_SESSION['user_id']) { // avoid self-delete
        $databaseConnection->query("DELETE FROM users WHERE id=$id");
    }
    header('Location: admin_user_management.php');
    exit;
}

$filter = $_GET['filter'] ?? 'all';
$where  = '';
if ($filter === 'admins')  $where = 'WHERE is_admin=1';
if ($filter === 'regular') $where = 'WHERE is_admin=0';

$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $safe = $databaseConnection->real_escape_string($search);
    $where .= ($where ? ' AND ' : 'WHERE ') . "(full_name LIKE '%$safe%' OR email LIKE '%$safe%')";
}

$result = $databaseConnection->query("SELECT * FROM users $where ORDER BY created_at DESC");
?>
<div class="page-header-main">
    <div class="page-header-title">User Management</div>
    <div class="page-header-sub">Manage registered accounts, roles, and system access.</div>
</div>

<div class="card mb-5 border-primary border-opacity-10">
    <div class="card-body">
        <form class="row g-3 align-items-center" method="get">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-dark border-end-0 border-secondary border-opacity-10">
                        <i class="bi bi-search text-secondary"></i>
                    </span>
                    <input type="text" name="search" class="form-control bg-dark border-start-0 ps-0" placeholder="Identity Search..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="filter" class="form-control">
                    <option value="all"     <?php echo $filter==='all'?'selected':''; ?>>All Access Roles</option>
                    <option value="admins"  <?php echo $filter==='admins'?'selected':''; ?>>Administrators</option>
                    <option value="regular" <?php echo $filter==='regular'?'selected':''; ?>>Standard Users</option>
                </select>
            </div>
            <div class="col-md-4">
                <div class="d-flex gap-2">
                    <button class="btn-primary w-100 py-2">
                        FILTER NODES
                    </button>
                    <a href="auth_register.php" class="btn btn-outline-info w-100 border-opacity-25">
                        <i class="bi bi-person-plus me-1"></i> ADD
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card p-0 overflow-hidden border-info border-opacity-10">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr>
                    <th class="ps-4 small opacity-50">ID</th>
                    <th class="small opacity-50">IDENTITY</th>
                    <th class="small opacity-50">NETWORK EMAIL</th>
                    <th class="small opacity-50">PRIVILEGE</th>
                    <th class="small opacity-50">REGISTRATION</th>
                    <th class="text-end pe-4 small opacity-50">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows): while ($u=$result->fetch_assoc()): ?>
                    <tr class="align-middle">
                        <td class="ps-4 py-3"><code class="text-info">#<?php echo $u['id']; ?></code></td>
                        <td class="fw-bold text-white"><?php echo htmlspecialchars($u['full_name']); ?></td>
                        <td><span class="text-secondary"><?php echo htmlspecialchars($u['email']); ?></span></td>
                        <td>
                            <?php if ($u['is_admin']): ?>
                                <span class="badge bg-primary bg-opacity-10 text-info border border-info border-opacity-25 px-3">ROOT ADMIN</span>
                            <?php else: ?>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3">OPERATOR</span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-secondary"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                        <td class="text-end pe-4">
                            <a href="?delete=<?php echo $u['id']; ?>" 
                               class="btn btn-sm btn-outline-danger border-opacity-25"
                               onclick="return confirm('Disconnect this operator node?')">
                                <i class="bi bi-shield-x"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="6" class="text-center py-5">No operator nodes detected.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
require_once 'helper_layout_footer.php';
?>
