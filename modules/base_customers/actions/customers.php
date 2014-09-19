<?php
//    Pastèque Web back office, Customers module
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

namespace BaseCustomers;

$srv = new \Pasteque\CustomersService();
if (isset($_POST['delete-customer'])) {
    $srv->delete($_POST['delete-customer']);
}

$customers = $srv->getAll(true);
?>

<!-- start bloc titre -->
<div class="blc_ti">

<h1><?php \pi18n("Customers", PLUGIN_NAME); ?></h1>
<span class="nb_article"><?php \pi18n("%d customers", PLUGIN_NAME, count($customers)); ?></span>

<?php \pi18n("Customer's diary", PLUGIN_NAME); ?>

<ul class="bt_fonction">
	<li><a class="bt_add transition" href="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'customer_edit'); ?>"><?php \pi18n("Add a customer", PLUGIN_NAME); ?></a></li>
</ul>


</div>
<!-- end bloc titre -->

<!-- start container scroll -->
<div class="container_scroll">
            
            	<div class="stick_row stickem-container">
                    
                    <!-- start colonne contenu -->
                    <div id="content_liste" class="grid_9">
                    
                        <div class="blc_content">



<table cellspacing="0" cellpadding="0">
	<thead>
		<tr>
			<th><?php \pi18n("Customer.number"); ?></th>
			<th><?php \pi18n("Customer.key"); ?></th>
			<th><?php \pi18n("Customer.dispName"); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php
foreach ($customers as $cust) {
?>
	<tr>
		<td><?php echo $cust->number; ?></td>
		<td><?php echo $cust->key; ?></td>
		<td><?php echo $cust->dispName; ?></td>
		<td class="edition">
			<a href="<?php echo \Pasteque\get_module_url_action(PLUGIN_NAME, 'customer_edit', array('id' => $cust->id)); ?>"><img src="<?php echo \Pasteque\get_template_url(); ?>img/edit.png" alt="<?php \pi18n('Edit'); ?>" title="<?php \pi18n('Edit'); ?>"></a>
			<form action="<?php echo \Pasteque\get_current_url(); ?>" method="post"><?php \Pasteque\form_delete("customer", $cust->id, \Pasteque\get_template_url() . 'img/delete.png') ?></form>
		</td>
	</tr>
<?php
}
?>
	</tbody>
</table>
</div></div>
                    <!-- end colonne contenu -->
                    
                    <!-- start sidebar menu -->
                    <div id="sidebar_menu" class="grid_3 stickem">
                    
                        <div class="blc_content">
                            
                            <!-- start texte editorial -->
                            <div class="edito"><!-- zone_edito --></div>
                            <!-- end texte editorial -->
                            
                            
                        </div>
                        
                    </div>
                    <!-- end sidebar menu -->
                    
        		</div>
                
        	</div>
            <!-- end container scroll -->