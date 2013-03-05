<?php
//    Pastèque Web back office, Users module
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

namespace BaseCashes;

$message = NULL;
$error = NULL;

$startStr = isset($_POST['start']) ? $_POST['start'] : \i18nDate(time() - 86400);
$stopStr = isset($_POST['stop']) ? $_POST['stop'] : \i18nDate(time());
// Set $start and $stop as timestamps
$startTime = \i18nRevDate($startStr);
$stopTime = \i18nRevDate($stopStr);
// Sql values
$start = \Pasteque\stdstrftime($startTime);
$stop = \Pasteque\stdstrftime($stopTime);

$sql = "SELECT CLOSEDCASH.HOST, CLOSEDCASH.DATESTART, "
        . "CLOSEDCASH.DATEEND, TICKETS.TICKETID, PRODUCTS.NAME AS PRD_NAME, "
        . "CATEGORIES.NAME AS CAT_NAME, "
        . "TICKETLINES.UNITS, TICKETLINES.PRICE * TICKETLINES.UNITS AS SELL, "
        . "TAXES.NAME, "
        . "TAXLINES.AMOUNT "
        . "FROM CLOSEDCASH "
        . "LEFT JOIN RECEIPTS ON RECEIPTS.MONEY = CLOSEDCASH.MONEY "
        . "LEFT JOIN TICKETS ON TICKETS.ID = RECEIPTS.ID "
        . "LEFT JOIN TICKETLINES ON TICKETLINES.TICKET = TICKETS.ID "
        . "LEFT JOIN PRODUCTS ON TICKETLINES.PRODUCT = PRODUCTS.ID "
        . "LEFT JOIN CATEGORIES ON PRODUCTS.CATEGORY = CATEGORIES.ID "
        . "LEFT JOIN TAXLINES ON TAXLINES.RECEIPT = TICKETS.ID "
        . "LEFT JOIN TAXES ON TAXLINES.TAXID = TAXES.ID "
        . "WHERE CLOSEDCASH.DATESTART > :start AND CLOSEDCASH.DATEEND < :stop "
        . "ORDER BY CLOSEDCASH.DATESTART DESC, TICKETS.TICKETID DESC, "
        . "TICKETLINES.LINE DESC";
$fields = array("HOST", "DATESTART", "DATEEND", "TICKETID", "PRD_NAME",
        "CAT_NAME", "UNITS", "SELL", "NAME", "AMOUNT");
$headers = array(\i18n("Session.host"), \i18n("Session.openDate"),
        \i18n("Session.closeDate"), \i18n("Ticket.number"),
        \i18n("Product name", PLUGIN_NAME), \i18n("Category name", PLUGIN_NAME),
        \i18n("Units", PLUGIN_NAME), \i18n("Sell", PLUGIN_NAME),
        \i18n("Tax name", PLUGIN_NAME), \i18n("Tax amount", PLUGIN_NAME));
$report = new \Pasteque\Report($sql);
$report->setParam(":start", $start);
$report->setParam(":stop", $stop);
$report->addFilter("DATESTART", "\Pasteque\stdtimefstr");
$report->addFilter("DATESTART", "\i18nDatetime");
$report->addFilter("DATEEND", "\Pasteque\stdtimefstr");
$report->addFilter("DATEEND", "\i18nDatetime");
?>
<h1><?php \pi18n("Sales report", PLUGIN_NAME); ?></h1>

<form class="edit" action="<?php echo \Pasteque\get_current_url(); ?>" method="post">
	<div class="row">
		<label for="start"><?php \pi18n("Session.openDate"); ?></label>
		<input type="date" name="start" id="start" value="<?php echo $startStr; ?>" />
	</div>
	<div class="row">
		<label for="stop"><?php \pi18n("Session.closeDate"); ?></label>
		<input type="date" name="stop" id="stop" value="<?php echo $stopStr; ?>" />
	</div>
	<div class="row actions">
		<?php \Pasteque\form_send(); ?>
	</div>
</form>

<?php \Pasteque\tpl_report($report, $fields, $headers); ?>
