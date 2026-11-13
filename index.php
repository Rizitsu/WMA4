<?php
declare(strict_types=1);
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rental POS System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <nav id="main-nav" style="display: none;">
        <button id="nav-dash" onclick="showSection('dashboard-section')">Dashboard</button>
        <button onclick="showSection('customers-section')">Manage Customers</button>
        <button onclick="showSection('inventory-section')">Manage Inventory</button>
        <button onclick="showSection('rentals-section')">Active Rentals & Returns</button>
        <button onclick="showSection('new-rental-section')">+ New Rental</button>
        <button onclick="logoutUser()" style="background: var(--danger); border-color: var(--danger); margin-left: auto;">Logout</button>
    </nav>
    <hr>

    <section id="login-section" style="display: none;">
        <h1>System Login</h1>
        <form id="login-form">
            <label>Username:</label><br>
            <input type="text" id="login_username" required><br><br>
            
            <label>Password:</label><br>
            <input type="password" id="login_password" required><br><br>
            
            <button type="submit">Log In</button>
        </form>
        <p id="login-error" style="color: var(--danger); display: none; font-size: 13px; margin-top: 10px;"></p>
    </section>

    <section id="dashboard-section" style="display: none;">
        <h1>Dashboard</h1>
        
        <h3>Inventory Status</h3>
        <ul>
            <li>Total Items: <span id="dash-total-items">0</span></li>
            <li>Available: <span id="dash-available">0</span></li>
            <li>Rented Out: <span id="dash-rented">0</span></li>
        </ul>

        <h3>Contracts</h3>
        <ul>
            <li>Active (On Time): <span id="dash-active">0</span></li>
            <li>Overdue: <span id="dash-overdue">0</span></li>
        </ul>

        <h3>Financials</h3>
        <ul>
            <li>Deposits Held: ₱<span id="dash-deposits">0.00</span></li>
            <li>Estimated Late Fees: ₱<span id="dash-late-fees">0.00</span></li>
        </ul>

        <h3>Sales Report</h3>
        <ul>
            <li>Completed Rentals<span id="dash-completed-rentals">0</span></li>
            <li>Base Rental Revenue<span>₱<span id="dash-base-revenue">0.00</span></span></li>
            <li>Late Fees Collected<span>₱<span id="dash-late-collected">0.00</span></span></li>
            <li>Total Revenue<span>₱<span id="dash-total-revenue">0.00</span></span></li>
            <li>Average Sale Value<span>₱<span id="dash-avg-sale">0.00</span></span></li>
            <li>Most Rented Item<span id="dash-top-item">—</span></li>
        </ul>

        <button>Refresh Dashboard</button>
    </section>

    <section id="customers-section" style="display: none;">
        <h1>Customers</h1>
        
        <form id="customer-form">
            <h3>Add / Edit Customer</h3>
            <input type="hidden" id="customer_id">
            
            <label>Full Name:</label><br>
            <input type="text" id="customer_name" required><br><br>
            
            <label>ID / License Number:</label><br>
            <input type="text" id="customer_id_number" required><br><br>
            
            <label>Phone:</label><br>
            <input type="text" id="customer_phone" required><br><br>
            
            <button type="submit">Save Customer</button>
        </form>

        <hr>

        <h3>Customer List</h3>
        <input type="text" id="customer-search" placeholder="Search customers...">
        <button onclick="search_customers()">Search</button>
        
        <table border="1">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>License #</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="customers-tbody">
                </tbody>
        </table>
    </section>

    <section id="inventory-section" style="display: none;">
        <h1>Inventory</h1>

        <form id="inventory-form">
            <h3>Add / Edit Item</h3>
            <input type="hidden" id="inventory_id">
            
            <label>Item Name:</label><br>
            <input type="text" id="item_name" required><br><br>
            
            <label>Serial Number:</label><br>
            <input type="text" id="serial_number" required><br><br>
            
            <label>Daily Rate (₱):</label><br>
            <input type="number" step="0.01" id="daily_rate" required><br><br>
            
            <label>Status:</label><br>
            <select id="item_status">
                <option value="Available">Available</option>
                <option value="Maintenance">Maintenance</option>
                </select><br><br>
            
            <button type="submit">Save Item</button>
        </form>

        <hr>

        <h3>Inventory List</h3>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Item Name</th>
                    <th>Serial #</th>
                    <th>Rate/Day</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="inventory-tbody">
                </tbody>
        </table>
    </section>

    <section id="new-rental-section" style="display: none;">
        <h1>Create New Rental Contract</h1>

        <form id="new-rental-form">
            <label>Select Customer:</label><br>
            <select id="rental_customer_id" required>
                <option value="">-- Choose Customer --</option>
            </select><br><br>

            <label>Checkout Date:</label><br>
            <input type="date" id="rental_checkout_date" required><br><br>

            <label>Expected Due Date:</label><br>
            <input type="date" id="rental_due_date" required><br><br>

            <label>Deposit Taken (₱):</label><br>
            <input type="number" step="0.01" id="rental_deposit" required><br><br>

            <label>Select Items to Rent (Hold CTRL/CMD to select multiple):</label><br>
            <select id="rental_inventory_ids" multiple required size="5">
                </select><br><br>

            <button type="submit">Process Rental Checkout</button>
        </form>
    </section>

    <section id="rentals-section" style="display: none;">
        <h1>Active Contracts</h1>

        <label>Filter:</label>
        <select id="contract-filter">
            <option value="Active">Active / Out</option>
            <option value="Overdue">Overdue Only</option>
            <option value="Returned">Past Returns</option>
        </select>
        <button>Filter</button>

        <table border="1">
            <thead>
                <tr>
                    <th>Contract #</th>
                    <th>Customer Name</th>
                    <th>Out Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="contracts-tbody">
                </tbody>
        </table>

        <hr>

        <div id="return-panel" style="border: 2px dashed black; padding: 10px; margin-top: 20px;">
            <h3>Process Return for Contract #<span id="return_contract_label"></span></h3>
            <form id="return-form">
                <input type="hidden" id="return_contract_id">
                
                <p>Check the items being returned right now:</p>
                <div id="return-checkboxes-container">
                    </div>
                <br>
                <button type="submit">Complete Return & Calculate Late Fees</button>
            </form>
        </div>
    </section>

<script>
    let csrf_token = '';
    let current_role = '';

    function showSection(sectionId) {
        document.querySelectorAll('section').forEach(sec => sec.style.display = 'none');
        document.getElementById(sectionId).style.display = 'block';

        if (sectionId === 'dashboard-section') load_dashboard();
        if (sectionId === 'customers-section') search_customers();
        if (sectionId === 'inventory-section') load_inventory();
        if (sectionId === 'new-rental-section') load_rental_dropdowns();
        if (sectionId === 'rentals-section') load_rentals();
    }

    // 0. Login handling
    document.getElementById('login-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const payload = {
            username: document.getElementById('login_username').value,
            password: document.getElementById('login_password').value
        };

        fetch('login.php', { 
            method: 'POST', 
            headers: { 'Content-Type': 'application/json' }, 
            body: JSON.stringify(payload) 
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                csrf_token = data.csrf_token;
                current_role = data.user.role;
                
                document.getElementById('main-nav').style.display = 'flex';
                document.getElementById('login-error').style.display = 'none';

                if (current_role === 'Staff') {
                    document.getElementById('nav-dash').style.display = 'none';
                    showSection('rentals-section');
                } else {
                    document.getElementById('nav-dash').style.display = 'inline-block';
                    showSection('dashboard-section');
                }
            } else {
                const err = document.getElementById('login-error');
                err.innerText = data.error || 'Invalid credentials.';
                err.style.display = 'block';
            }
        });
    });

    document.getElementById('return-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const contractId = document.getElementById('return_contract_id').value;
        if (!contractId) {
            alert('Please select a contract first.');
            return;
        }

        const payload = {
            contract_id: contractId
        };

        fetch('process_return.php', { 
            method: 'POST', 
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrf_token 
            }, 
            body: JSON.stringify(payload) 
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Items returned successfully! Late fee applied: ₱' + (data.late_fee || 0));
                document.getElementById('return-form').reset();
                document.getElementById('return_contract_label').innerText = '';
                load_rentals();
            } else {
                alert('Error: ' + (data.error || 'Failed to process return.'));
            }
        })
        .catch(err => {
            alert('Network or server error occurred.');
        });
    });

    // 0b. Global Logout function
    function logoutUser() {
        fetch('logout.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                csrf_token = '';
                current_role = '';
                document.getElementById('login-form').reset();
                document.getElementById('main-nav').style.display = 'none';
                showSection('login-section');
            }
        });
    }

    // 1. dashboard
    function load_dashboard() {
        fetch('dashboard.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const s = data.summary;
                    document.getElementById('dash-total-items').innerText = s.inventory.total_items;
                    document.getElementById('dash-available').innerText = s.inventory.available;
                    document.getElementById('dash-rented').innerText = s.inventory.rented;
                    
                    document.getElementById('dash-active').innerText = s.contracts.active_on_time;
                    document.getElementById('dash-overdue').innerText = s.contracts.overdue;
                    
                    document.getElementById('dash-deposits').innerText = s.financial.deposits_held.toFixed(2);
                    document.getElementById('dash-late-fees').innerText = s.financial.late_fee_exposure.toFixed(2);

                    document.getElementById('dash-completed-rentals').innerText = s.sales.completed_rentals;
                    document.getElementById('dash-base-revenue').innerText = s.sales.base_rental_revenue.toFixed(2);
                    document.getElementById('dash-late-collected').innerText = s.sales.late_fees_collected.toFixed(2);
                    document.getElementById('dash-total-revenue').innerText = s.sales.total_revenue.toFixed(2);
                    document.getElementById('dash-avg-sale').innerText = s.sales.average_sale_value.toFixed(2);
                    document.getElementById('dash-top-item').innerText = s.sales.top_item
                        ? s.sales.top_item + ' (' + s.sales.top_item_rentals + 'x)'
                        : '—';
                }
            });
    }

    document.querySelector('#dashboard-section button').addEventListener('click', load_dashboard);


    // 2. customer
    function search_customers() {
        const term = document.getElementById('customer-search').value;
        fetch('customers.php?search=' + encodeURIComponent(term))
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('customers-tbody');
                tbody.innerHTML = '';
                if (data.success && data.results) {
                    data.results.forEach((c, index) => {
                        let row_num = index + 1;
                        let actionBtns = current_role === 'Admin' 
                            ? `<button onclick="edit_customer(${c.id})">Edit</button> <button onclick="delete_customer(${c.id})">Delete</button>`
                            : `<button onclick="edit_customer(${c.id})">Edit</button>`;

                        tbody.innerHTML += `<tr>
                            <td>${row_num}</td>
                            <td>${c.name}</td>
                            <td>${c.id_number}</td>
                            <td>${c.phone}</td>
                            <td>${actionBtns}</td>
                        </tr>`;
                    });
                }
            });
    }

    function delete_customer(id) {
        if (!confirm('Delete this customer? This cannot be undone.')) return;
        fetch('customers.php?id=' + id, { 
            method: 'DELETE',
            headers: { 'X-CSRF-Token': csrf_token }
        })
        .then(res => res.json())
        .then(data => { data.success ? search_customers() : alert(data.error || 'Delete failed.'); });
    }
    
    function delete_inventory(id) {
        if (!confirm('Delete this inventory item? This cannot be undone.')) return;
        fetch('inventory.php?id=' + id, { 
            method: 'DELETE',
            headers: { 'X-CSRF-Token': csrf_token }
        })
        .then(res => res.json())
        .then(data => { data.success ? load_inventory() : alert(data.error || 'Delete failed.'); });
    }

   //2b. inventory
    function load_inventory() {
        fetch('inventory.php')
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('inventory-tbody');
                tbody.innerHTML = '';
                if (data.success && data.results) {
                    data.results.forEach((i, index) => {
                        let row_num = index + 1; 
                        let actionBtns = current_role === 'Admin' 
                            ? `<button onclick="edit_inventory(${i.id})">Edit</button> <button onclick="delete_inventory(${i.id})">Delete</button>`
                            : `<button onclick="edit_inventory(${i.id})">Edit</button>`;

                        tbody.innerHTML += `<tr>
                            <td>${row_num}</td>
                            <td>${i.item_name}</td>
                            <td>${i.serial_number}</td>
                            <td>₱${i.daily_rate}</td>
                            <td>${i.status}</td>
                            <td>${actionBtns}</td>
                        </tr>`;
                    });
                }
            });
    }

    document.getElementById('customer-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('customer_id').value;
        const payload = {
            name: document.getElementById('customer_name').value,
            id_number: document.getElementById('customer_id_number').value,
            phone: document.getElementById('customer_phone').value
        };
        const method = id ? 'PUT' : 'POST';
        const url = id ? 'customers.php?id=' + id : 'customers.php';

        fetch(url, { 
            method: method, 
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrf_token 
            }, 
            body: JSON.stringify(payload) 
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(id ? 'Updated!' : 'Created!');
                document.getElementById('customer-form').reset();
                document.getElementById('customer_id').value = '';
                search_customers();
            } else alert('Error: ' + data.error);
        });
    });


    // 3. inventory magics
    function load_rentals() {
        const status = document.getElementById('contract-filter').value;
        fetch('active_rentals.php?status=' + status)
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('contracts-tbody');
                tbody.innerHTML = '';
                if (data.success && data.results) {
                    data.results.forEach((c, index) => {
                        let row_num = index + 1;
                        let actionBtn = c.status !== 'Returned' 
                            ? `<button onclick="process_full_return(${c.id})">Return All Items</button>` 
                            : 'Closed';
                        
                        tbody.innerHTML += `<tr>
                            <td>${row_num}</td>
                            <td>${c.customer_name}</td>
                            <td>${c.checkout_date}</td>
                            <td>${c.due_date}</td>
                            <td>${c.status}</td>
                            <td>${actionBtn}</td>
                        </tr>`;
                    });
                }
            });
    }

    function edit_inventory(id) {
        fetch('inventory.php?id=' + id).then(res => res.json()).then(data => {
            if (data.success) {
                document.getElementById('inventory_id').value = data.item.id;
                document.getElementById('item_name').value = data.item.item_name;
                document.getElementById('serial_number').value = data.item.serial_number;
                document.getElementById('daily_rate').value = data.item.daily_rate;
                document.getElementById('item_status').value = data.item.status;
                window.scrollTo(0, 0);
            }
        });
    }

    document.getElementById('inventory-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('inventory_id').value;
        const payload = {
            item_name: document.getElementById('item_name').value,
            serial_number: document.getElementById('serial_number').value,
            daily_rate: document.getElementById('daily_rate').value,
            status: document.getElementById('item_status').value
        };
        const method = id ? 'PUT' : 'POST';
        const url = id ? 'inventory.php?id=' + id : 'inventory.php';

        fetch(url, { 
            method: method, 
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrf_token 
            }, 
            body: JSON.stringify(payload) 
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(id ? 'Item Updated!' : 'Item Saved!');
                document.getElementById('inventory-form').reset();
                document.getElementById('inventory_id').value = '';
                load_inventory();
            } else alert('Error: ' + data.error);
        });
    });


    // 4. new rentals
    function load_rental_dropdowns() {
        fetch('customers.php').then(res => res.json()).then(data => {
            const sel = document.getElementById('rental_customer_id');
            sel.innerHTML = '<option value="">-- Choose Customer --</option>';
            if (data.success && data.results) {
                data.results.forEach(c => sel.innerHTML += `<option value="${c.id}">${c.name}</option>`);
            }
        });

        fetch('inventory.php?status=Available').then(res => res.json()).then(data => {
            const sel = document.getElementById('rental_inventory_ids');
            sel.innerHTML = '';
            if (data.success && data.results) {
                data.results.forEach(i => sel.innerHTML += `<option value="${i.id}">${i.item_name} (₱${i.daily_rate}/day)</option>`);
            }
        });
    }

    document.getElementById('new-rental-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const selectedOptions = Array.from(document.getElementById('rental_inventory_ids').selectedOptions);
        
        const payload = {
            customer_id: document.getElementById('rental_customer_id').value,
            checkout_date: document.getElementById('rental_checkout_date').value,
            due_date: document.getElementById('rental_due_date').value,
            deposit_amount: document.getElementById('rental_deposit').value,
            inventory_ids: selectedOptions.map(opt => opt.value)
        };

        fetch('process_new_rental.php', { 
            method: 'POST', 
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrf_token 
            }, 
            body: JSON.stringify(payload) 
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Rental created! Contract ID: ' + data.contract_id);
                document.getElementById('new-rental-form').reset();
                load_rental_dropdowns();
            } else alert('Error: ' + data.error);
        });
    });

    // 5. active rentals
    document.querySelector('#rentals-section button').addEventListener('click', load_rentals);

    function process_full_return(contract_id) {
        if (confirm('Are you sure you want to process a full return for Contract #' + contract_id + '?')) {
            fetch('process_return.php', { 
                method: 'POST', 
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrf_token 
                }, 
                body: JSON.stringify({ contract_id: contract_id }) 
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Items returned! Late fee applied: ₱' + data.late_fee);
                    load_rentals();
                } else {
                    alert('Error: ' + data.error);
                }
            });
        }
    }

    function edit_customer(id) {
        fetch('customers.php?id=' + id).then(res => res.json()).then(data => {
            if (data.success) {
                document.getElementById('customer_id').value = data.customer.id;
                document.getElementById('customer_name').value = data.customer.name;
                document.getElementById('customer_id_number').value = data.customer.id_number;
                document.getElementById('customer_phone').value = data.customer.phone;
                window.scrollTo(0, 0);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => showSection('login-section'));
</script>

</body>
</html>
