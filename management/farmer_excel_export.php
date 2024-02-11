<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");

header("Content-Type: application/xls");
header("Content-Disposition: attachment; filename=report-" . date('d-M-y') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");


$query = "SELECT kf.`id`,kf.`name_title`, kf.`name`, kf.`parent_name_sub_title`, kf.`parent_name`, kf.`parent_name_relation`, kf.`mobile`, kf.`village`, kf.`khashra_no`, kf.`area_hectare`, kf.`area_sqft`, kf.`total_area_biswa`, kf.`per_biswa`, kf.`plot_value`, kf.`registry`, kf.`registry_date`, kf.`purchaser_name`,kft.`farmer_id`, kft.`amount`, kft.`payment_type`, kft.`bank_name`, kft.`cheque_dd_number`, kft.`cr_dr`, kft.`paid_date`, kft.`next_due_date`, kft.`status`, kft.`remarks` FROM `kc_farmers` kf LEFT JOIN `kc_farmer_transactions` kft on  kf.`id` = kft.`farmer_id` WHERE kft.`cr_dr` = 'cr'";

// echo "<pre>"; print_r ($query);die;
                                                            

if (isset($_GET['search_farmer']) && $_GET['search_farmer'] != '') {

    //$query .= " and name LIKE '%".$_GET['search_farmer']."%'";
    if (!ctype_digit($_GET['search_farmer'])) {
        $query .= " and kf.name LIKE '%" . $_GET['search_farmer'] . "%'";
    } else {
        $query .= " and kf.id = '" . $_GET['search_farmer'] . "'";
    }
}



$query .= " order by kf.id desc";
// echo "<pre>";print_r($query);die;
$farmers = mysqli_query($conn, $query);


if (mysqli_num_rows($farmers) >= 0) {
    echo '<table border="1">';
    //make the column headers what you want in whatever order you want
    echo    '<tr><th>Sr.</th>
                <th>Name</th>
                <th>Relation</th>
                <th>Relative</th>
                <th>Mobile No</th>
                <th>Village</th>
                <th>Khasra No</th>
                <th>Area(In Hectare)</th>
                <th>Area(In Sq.ft)</th>
                <th>Area(In Biswa)</th>
                <th>Total Plot Value</th>
                <th>Rate Per Biswa</th>
                <th>Registry</th>
                <th>Purchaser Name</th>
                <th>Registry Date</th>
              </tr>';
    //loop the query data to the table in same order as the headers
    $counter = 1;
    $total_debited_amt = $total_credited_amt = $total_pending_amt = 0;
    while ($farmer = mysqli_fetch_assoc($farmers)) {
        

        // $registry_date = date("Y-d-m",strtotime($farmer['registry_date']));


        // echo "<pre>";print_r($registry_date);die;

        // echo "<pre>"; print_r(farmerID($farmer['farmer_id'])); die;
        // $total_debited_amt += $total_debited = totalDebited($conn,$farmer['farmer_id']);
        // $total_credited_amt += $total_credited = totalCredited($conn,$farmer['farmer_id']);

        // $total_pending_amt += $pending_amount = ($total_credited - $total_debited);

    ?>
        <tr>
            <td><?php echo $counter; ?>.</td>
            <td>
                <?php echo $farmer['name_title']; ?> <?php echo $farmer['name'] . ' (' . farmerID($farmer['id']) . ')'; ?>
            </td>

            <td>
                <strong><?php echo $farmer['parent_name_relation']; ?></strong> 
            </td>
            
            <td>
                <strong><?php echo isset($farmer['parent_name_sub_title']) ? $farmer['parent_name_sub_title'] : ''; ?> <?php echo $farmer['parent_name'];
                 ?></strong><br>
            </td>

            
            <td>
                <?php echo $farmer['mobile']; ?><br>
            </td>

            <td>
                <?php echo $farmer['village']; ?><br>
            </td>

            <td>
                <?php echo $farmer['khashra_no']; ?>
            </td>
            <td>
                <?php echo $farmer['area_hectare']; ?><br>
            </td>
            <td>
                <!-- <?php echo $farmer['area_sqft']; ?><br> -->
                <?php echo isset($farmer['area_sqft'])?$farmer['area_sqft']:107639*$farmer['area_hectare'];?>
            </td>
            <td>
                <?php echo $farmer['total_area_biswa']; ?>
            </td>
            <td>
                <?php echo $farmer['plot_value']; ?><br>

            </td>
            <td>
                <?php echo $farmer['per_biswa']; ?><br>
            </td>
            <td>
                <!-- <?php echo isset($farmer['registry'])?$farmer['registry']:'No'; ?><br> -->
                <?php if(isset($farmer['registry_date'])){ echo $farmer['registry']='Yes';}
                else {
                    echo $farmer['registry']='No';
                }?>
            </td>
            <td>
                <?php echo $farmer['purchaser_name']; ?><br>
            </td>
            <td>
                <?php echo isset($farmer['registry_date'])?date("d-m-Y",strtotime($farmer['registry_date'])):'';?><br>
            </td>

        </tr>
    <?php $counter++;
    }
}
?>
