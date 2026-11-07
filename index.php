<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Ustudent - Student Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
        }

        .navbar {
            background: #ffffff;
        }

        .navbar-brand {
            font-size: 25px;
            font-weight: bold;
            color: #0d6efd;
        }

        .hero {
            background: linear-gradient(120deg, #0d6efd, #003b95);
            color: white;
            padding: 70px 0 90px;
        }

        .hero h1 {
            font-size: 42px;
            font-weight: bold;
        }

        .stats {
            margin-top: -55px;
        }

        .stat-box {
            background: white;
            padding: 22px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.10);
        }

        .stat-box h2 {
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 0;
        }

        .panel {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        .section-title {
            font-weight: bold;
            color: #222;
        }

        .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #0d6efd;
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }

        footer {
            background: #111827;
            color: white;
            padding: 35px 0;
        }
    </style>
</head>

<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg shadow-sm">
    <div class="container">

        <a class="navbar-brand" href="index.html">
            🎓 Ustudent
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav ms-auto align-items-lg-center">

                <li class="nav-item">
                    <a class="nav-link active" href="#top">Home</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#students">Students</a>
                </li>

                <li class="nav-item ms-lg-2" id="authGuest">
                    <button class="btn btn-outline-primary px-4 me-2"> <a href="admin/index.php">
                        Login </a>
                    </button>
                    <button class="btn btn-primary px-4" onclick="authModal('register')">
                        Register
                    </button>
                </li>

                <li class="nav-item ms-lg-2 d-none" id="authUser">
                    <span class="me-2 fw-semibold" id="userName"></span>
                    <button class="btn btn-outline-danger px-4" onclick="logout()">
                        Logout
                    </button>
                </li>

            </ul>
        </div>
    </div>
</nav>


<!-- Hero -->
<section class="hero" id="top">
    <div class="container text-center">
        <h1>Ustudent</h1>
        <p class="lead mt-3">
            Student Management System: add, update, search and manage student records in one place.
        </p>
    </div>
</section>


<!-- Stats -->
<section class="stats">
    <div class="container">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="stat-box">
                    <h2 id="statTotal">0</h2>
                    <p class="text-muted mb-0">Total Students</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box">
                    <h2 id="statDepts">0</h2>
                    <p class="text-muted mb-0">Departments</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box">
                    <h2 id="statCgpa">0.00</h2>
                    <p class="text-muted mb-0">Average CGPA</p>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- Student List -->
<section class="py-5" id="students">
    <div class="container">

        <div class="panel">

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <h3 class="section-title mb-0">All Students</h3>
                <div>
                    <button class="btn btn-outline-secondary me-2" onclick="exportCSV()">Export CSV</button>
                    <button class="btn btn-primary" onclick="openAdd()">+ Add Student</button>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-8">
                    <input type="text" id="searchInput" class="form-control"
                           placeholder="Search by name, ID or email..." oninput="render()">
                </div>
                <div class="col-md-4">
                    <select id="deptFilter" class="form-select" onchange="render()">
                        <option value="">All Departments</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th>ID</th>
                            <th>Department</th>
                            <th>Semester</th>
                            <th>CGPA</th>
                            <th>Phone</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="studentTable"></tbody>
                </table>
            </div>

            <p id="emptyMsg" class="text-center text-muted py-4 d-none">
                No students found.
            </p>

        </div>

    </div>
</section>


<!-- Add / Edit Modal -->
<div class="modal fade" id="studentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form id="studentForm" onsubmit="saveStudent(event)">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="editId">

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" id="name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Student ID</label>
                            <input type="text" id="studentId" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" id="email" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" id="phone" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Department</label>
                            <select id="department" class="form-select" required>
                                <option value="">Select</option>
                                <option>CSE</option>
                                <option>EEE</option>
                                <option>BBA</option>
                                <option>English</option>
                                <option>Civil</option>
                                <option>Mathematics</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Semester</label>
                            <select id="semester" class="form-select" required>
                                <option value="">Select</option>
                                <option>1st</option>
                                <option>2nd</option>
                                <option>3rd</option>
                                <option>4th</option>
                                <option>5th</option>
                                <option>6th</option>
                                <option>7th</option>
                                <option>8th</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">CGPA</label>
                            <input type="number" id="cgpa" class="form-control"
                                   min="0" max="4" step="0.01" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea id="address" class="form-control" rows="2"></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>

            </form>

        </div>
    </div>
</div>


<!-- Login / Register Modal -->
<div class="modal fade" id="authModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authTitle">Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Login form -->
            <!-- <form id="loginForm" onsubmit="doLogin(event)">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" id="loginEmail" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" id="loginPass" class="form-control" required>
                    </div>
                    <div class="text-danger small" id="loginErr"></div>
                    <p class="small text-muted mt-3 mb-0">
                        No account? <a href="#" onclick="authModal('register'); return false;">Register</a>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </div>
            </form> -->

            <!-- Register form -->
            <!-- <form id="registerForm" class="d-none" onsubmit="doRegister(event)">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" id="regName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" id="regEmail" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" id="regPass" class="form-control" minlength="6" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" id="regPass2" class="form-control" minlength="6" required>
                    </div>
                    <div class="text-danger small" id="regErr"></div>
                    <p class="small text-muted mt-3 mb-0">
                        Already have an account? <a href="#" onclick="authModal('login'); return false;">Login</a>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Create Account</button>
                </div>
            </form> -->

        </div>
    </div>
</div>


<!-- Footer -->
<footer>
    <div class="container text-center">

        <h4>🎓 Ustudent</h4>

        <p class="text-secondary">
            Student Management System
        </p>

        <hr>

        <p class="mb-0">
            © 2026 Ustudent. All Rights Reserved.
        </p>

    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const KEY = "students_data";
    let students = [];

    // Load saved data (falls back to sample data the first time)
    function load() {
        try {
            const saved = localStorage.getItem(KEY);
            if (saved) { students = JSON.parse(saved); return; }
        } catch (e) {}
        students = [
            { id: 1, name: "Rahim Uddin", studentId: "CSE-001", email: "rahim@example.com", phone: "01711000001", department: "CSE", semester: "5th", cgpa: 3.75, address: "Dhaka" },
            { id: 2, name: "Nusrat Jahan", studentId: "EEE-014", email: "nusrat@example.com", phone: "01811000002", department: "EEE", semester: "3rd", cgpa: 3.52, address: "Bogura" },
            { id: 3, name: "Karim Hasan", studentId: "BBA-023", email: "karim@example.com", phone: "01911000003", department: "BBA", semester: "7th", cgpa: 3.10, address: "Sylhet" }
        ];
        persist();
    }

    function persist() {
        try { localStorage.setItem(KEY, JSON.stringify(students)); } catch (e) {}
    }

    const modal = new bootstrap.Modal(document.getElementById("studentModal"));

    function esc(text) {
        const d = document.createElement("div");
        d.textContent = text == null ? "" : text;
        return d.innerHTML;
    }

    function openAdd() {
        document.getElementById("studentForm").reset();
        document.getElementById("editId").value = "";
        document.getElementById("modalTitle").textContent = "Add Student";
        modal.show();
    }

    function openEdit(id) {
        const s = students.find(x => x.id === id);
        if (!s) return;
        document.getElementById("editId").value = s.id;
        document.getElementById("name").value = s.name;
        document.getElementById("studentId").value = s.studentId;
        document.getElementById("email").value = s.email;
        document.getElementById("phone").value = s.phone;
        document.getElementById("department").value = s.department;
        document.getElementById("semester").value = s.semester;
        document.getElementById("cgpa").value = s.cgpa;
        document.getElementById("address").value = s.address;
        document.getElementById("modalTitle").textContent = "Edit Student";
        modal.show();
    }

    function saveStudent(e) {
        e.preventDefault();

        const editId = document.getElementById("editId").value;
        const data = {
            name: document.getElementById("name").value.trim(),
            studentId: document.getElementById("studentId").value.trim(),
            email: document.getElementById("email").value.trim(),
            phone: document.getElementById("phone").value.trim(),
            department: document.getElementById("department").value,
            semester: document.getElementById("semester").value,
            cgpa: parseFloat(document.getElementById("cgpa").value),
            address: document.getElementById("address").value.trim()
        };

        // Student ID must be unique
        const duplicate = students.some(s =>
            s.studentId.toLowerCase() === data.studentId.toLowerCase() &&
            String(s.id) !== editId
        );
        if (duplicate) {
            alert("This Student ID already exists.");
            return;
        }

        if (editId) {
            const i = students.findIndex(s => String(s.id) === editId);
            students[i] = { id: students[i].id, ...data };
        } else {
            const nextId = students.length ? Math.max(...students.map(s => s.id)) + 1 : 1;
            students.push({ id: nextId, ...data });
        }

        persist();
        modal.hide();
        render();
    }

    function deleteStudent(id) {
        const s = students.find(x => x.id === id);
        if (!s) return;
        if (confirm("Delete " + s.name + "?")) {
            students = students.filter(x => x.id !== id);
            persist();
            render();
        }
    }

    function updateFilter() {
        const select = document.getElementById("deptFilter");
        const current = select.value;
        const depts = [...new Set(students.map(s => s.department))].sort();
        select.innerHTML = '<option value="">All Departments</option>' +
            depts.map(d => '<option>' + esc(d) + '</option>').join("");
        select.value = depts.includes(current) ? current : "";
    }

    function updateStats() {
        document.getElementById("statTotal").textContent = students.length;
        document.getElementById("statDepts").textContent =
            new Set(students.map(s => s.department)).size;
        const avg = students.length
            ? students.reduce((sum, s) => sum + s.cgpa, 0) / students.length
            : 0;
        document.getElementById("statCgpa").textContent = avg.toFixed(2);
    }

    function render() {
        updateFilter();
        updateStats();

        const q = document.getElementById("searchInput").value.toLowerCase().trim();
        const dept = document.getElementById("deptFilter").value;

        const list = students.filter(s => {
            const match = !q ||
                s.name.toLowerCase().includes(q) ||
                s.studentId.toLowerCase().includes(q) ||
                s.email.toLowerCase().includes(q);
            return match && (!dept || s.department === dept);
        });

        document.getElementById("studentTable").innerHTML = list.map(s => `
            <tr>
                <td>
                    <span class="avatar">${esc(s.name.charAt(0).toUpperCase())}</span>
                    ${esc(s.name)}
                    <div class="small text-muted">${esc(s.email)}</div>
                </td>
                <td>${esc(s.studentId)}</td>
                <td><span class="badge bg-primary">${esc(s.department)}</span></td>
                <td>${esc(s.semester)}</td>
                <td>${Number(s.cgpa).toFixed(2)}</td>
                <td>${esc(s.phone)}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-primary" onclick="openEdit(${s.id})">Edit</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteStudent(${s.id})">Delete</button>
                </td>
            </tr>
        `).join("");

        document.getElementById("emptyMsg").classList.toggle("d-none", list.length > 0);
    }

    function exportCSV() {
        const header = ["Name", "Student ID", "Email", "Phone", "Department", "Semester", "CGPA", "Address"];
        const rows = students.map(s =>
            [s.name, s.studentId, s.email, s.phone, s.department, s.semester, s.cgpa, s.address]
                .map(v => '"' + String(v == null ? "" : v).replace(/"/g, '""') + '"').join(",")
        );
        const csv = [header.join(","), ...rows].join("\n");
        const blob = new Blob([csv], { type: "text/csv" });
        const a = document.createElement("a");
        a.href = URL.createObjectURL(blob);
        a.download = "students.csv";
        a.click();
        URL.revokeObjectURL(a.href);
    }

    // ---------- Login / Register (front-end demo, stored in this browser) ----------
    const USERS_KEY = "ustudent_users";
    const SESSION_KEY = "ustudent_session";
    const authBox = new bootstrap.Modal(document.getElementById("authModal"));

    function getUsers() {
        try { return JSON.parse(localStorage.getItem(USERS_KEY)) || []; } catch (e) { return []; }
    }

    function authModal(mode) {
        const isLogin = mode === "login";
        document.getElementById("authTitle").textContent = isLogin ? "Login" : "Register";
        document.getElementById("loginForm").classList.toggle("d-none", !isLogin);
        document.getElementById("registerForm").classList.toggle("d-none", isLogin);
        document.getElementById("loginErr").textContent = "";
        document.getElementById("regErr").textContent = "";
        authBox.show();
    }

    function doRegister(e) {
        e.preventDefault();
        const name = document.getElementById("regName").value.trim();
        const email = document.getElementById("regEmail").value.trim().toLowerCase();
        const pass = document.getElementById("regPass").value;
        const pass2 = document.getElementById("regPass2").value;
        const err = document.getElementById("regErr");

        if (pass !== pass2) { err.textContent = "Passwords do not match."; return; }

        const users = getUsers();
        if (users.some(u => u.email === email)) {
            err.textContent = "This email is already registered.";
            return;
        }

        users.push({ name, email, pass });
        try {
            localStorage.setItem(USERS_KEY, JSON.stringify(users));
            localStorage.setItem(SESSION_KEY, email);
        } catch (x) {}
        document.getElementById("registerForm").reset();
        authBox.hide();
        showUser();
    }

    function doLogin(e) {
        e.preventDefault();
        const email = document.getElementById("loginEmail").value.trim().toLowerCase();
        const pass = document.getElementById("loginPass").value;
        const user = getUsers().find(u => u.email === email && u.pass === pass);

        if (!user) {
            document.getElementById("loginErr").textContent = "Wrong email or password.";
            return;
        }
        try { localStorage.setItem(SESSION_KEY, email); } catch (x) {}
        document.getElementById("loginForm").reset();
        authBox.hide();
        showUser();
    }

    function logout() {
        try { localStorage.removeItem(SESSION_KEY); } catch (x) {}
        showUser();
    }

    function showUser() {
        let email = null;
        try { email = localStorage.getItem(SESSION_KEY); } catch (x) {}
        const user = email ? getUsers().find(u => u.email === email) : null;

        document.getElementById("authGuest").classList.toggle("d-none", !!user);
        document.getElementById("authUser").classList.toggle("d-none", !user);
        document.getElementById("userName").textContent = user ? "Hi, " + user.name : "";
    }

    load();
    render();
    showUser();
</script>

</body>
</html>