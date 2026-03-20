<div id="menu">
    <button id="menu-toggle">☰</button>
    <div id="menu-content">
        <a href="https://myscalea.it">⬅ Back to MyScalea</a>
        <div id="search-container" style="margin: 10px 0;">
            <input type="text" id="searchInput" placeholder="Search places..." style="width: 100%; padding: 5px;">
        </div>
        <p class="search-hint">
  ⚠️ Search works only on original English names/descriptions.
</p>
        <div id="category-filter">
            <strong>📂 Categories:</strong><br>
            <?php
            $main_categories = [];
            
            foreach ($categories as $category) {
                $parent_id = $category['parent_category_id'] ?? null;
                $parent_name = $category['parent_name'] ?? "Other";
                
                if (!isset($main_categories[$parent_id])) {
                    $main_categories[$parent_id] = [
                        'name' => $parent_name,
                        'subcategories' => []
                    ];
                }
                
                $main_categories[$parent_id]['subcategories'][] = $category;
            }
            ?>
            
            <?php foreach ($main_categories as $main_id => $main_category): ?>
                <div class="category-group">
                    <label>
                        <input type="checkbox" class="group-filter" data-group="<?php echo $main_id; ?>" checked>
                        <strong><?php echo $main_category['name']; ?></strong>
                        <span class="toggle-sub">▼</span>
                    </label>
                    <div class="subcategories" style="display: none;">
                        <?php foreach ($main_category['subcategories'] as $category): ?>
                            <label>
                                <input type="checkbox" class="category-filter" data-category="<?php echo $category['fk_i_category_id']; ?>" checked>
                                <?php echo $category['category_name']; ?>
                            </label><br>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    document.getElementById("menu-toggle").addEventListener("click", function() {
        var menu = document.getElementById("menu-content");
        menu.style.display = menu.style.display === "block" ? "none" : "block";
    });

    // Toggle subcategories while keeping their state
    document.querySelectorAll(".toggle-sub").forEach(button => {
        button.addEventListener("click", function() {
            var subcategories = this.parentElement.nextElementSibling;
            subcategories.style.display = subcategories.style.display === "block" ? "none" : "block";
        });
    });

    // Enable/disable all subcategories when clicking on a group
    document.querySelectorAll(".group-filter").forEach(groupCheckbox => {
        groupCheckbox.addEventListener("change", function() {
            var allSub = this.closest(".category-group").querySelectorAll(".category-filter");
            allSub.forEach(cb => {
                cb.checked = this.checked;
                toggleCategory(cb.dataset.category, cb.checked);
            });
            updateGroupCheckboxState(this, allSub);
        });
    });

    // Update parent category state when subcategories change and update points
    document.querySelectorAll(".category-filter").forEach(categoryCheckbox => {
        categoryCheckbox.addEventListener("change", function() {
            var parentCheckbox = this.closest(".category-group").querySelector(".group-filter");
            var allSub = this.closest(".subcategories").querySelectorAll(".category-filter");
            updateGroupCheckboxState(parentCheckbox, allSub);
            toggleCategory(this.dataset.category, this.checked);
        });
    });

    function updateGroupCheckboxState(parentCheckbox, subcategories) {
        var checkedCount = 0;
        subcategories.forEach(sub => { if (sub.checked) checkedCount++; });
        if (checkedCount === 0) {
            parentCheckbox.checked = false;
            parentCheckbox.indeterminate = false;
        } else if (checkedCount === subcategories.length) {
            parentCheckbox.checked = true;
            parentCheckbox.indeterminate = false;
        } else {
            parentCheckbox.checked = false;
            parentCheckbox.indeterminate = true;
        }
    }

    function toggleCategory(categoryId, show) {
        if (window.markers && markers[categoryId]) {
            markers[categoryId].forEach(marker => {
                show ? map.addLayer(marker) : map.removeLayer(marker);
            });
        }
    }
</script>
