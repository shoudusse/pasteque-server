<?php
//    Pastèque Web back office, Products module
//
//    Copyright (C) 2013 Scil (http://scil.coop)
//
//    This file is part of Pastèque.
//
//    Pastèque is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    Pastèque is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with Pastèque.  If not, see <http://www.gnu.org/licenses/>.

// category_edit action

namespace BaseProducts;

if (isset($_POST['id']) && isset($_POST['label'])) {
    $cat = \Pasteque\Category::__build($_POST['id'], NULL,
            $_POST['label']);
    \Pasteque\CategoriesService::updateCat($cat);
} else if (isset($_POST['label'])) {
    $cat = new \Pasteque\Category(NULL, $_POST['label']);
    \Pasteque\CategoriesService::createCat($cat);
}

$category = NULL;
if (isset($_GET['id'])) {
    $category = \Pasteque\CategoriesService::get($_GET['id']);
}
?>
<h1><?php \pi18n("Edit a category", PLUGIN_NAME); ?></h1>

<form class="edit" action="<?php echo \Pasteque\get_current_url(); ?>" method="post">
    <?php \Pasteque\form_hidden("edit", $category, "id"); ?>
	<?php \Pasteque\form_input("edit", "Category", $category, "label", "string", array("required" => true)); ?>
	<div class="row actions">
		<?php \Pasteque\form_send(); ?>
	</div>
</form>
<?php if ($category !== NULL) { ?>
<form action="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'categories'); ?>" method="post">
    <?php \Pasteque\form_delete("cat", $category->id); ?>
</form>
<?php } ?>
