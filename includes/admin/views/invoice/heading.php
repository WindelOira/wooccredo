<?php 
defined('ABSPATH') || exit;
?>
<table class="wooccredo-table">
    <tbody>
        <tr>
            <th width="120">Period</th>
            <td width="120"><?php echo self::$invoice['PeriodName']; ?></td>
            <th width="20">ID</th>
            <td width="120"><?php echo self::$invoice['DocumentID']; ?></td>
        </tr>
        <tr>
            <th width="120">Customer Code</th>
            <td width="120"><?php echo self::$invoice['CustomerCode']; ?></td>
        </tr>
    </tbody>
</table>