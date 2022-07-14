

<?php error_reporting(0);
$html = '<link rel="stylesheet" href="pdf.css"><style>
body{
	width: 100%;
    margin: 0;
    padding: 0;
}
.header{
	width: 100%;
	/*border-bottom: 1px solid #015bba;*/
}

.footer_tag{
    border-top: 1px solid #015bba;
	width: 100%;
}
.footer_tag .calling{
	width: 50%;
	text-align: left;
	color:#0264c9;
	float: left;
	padding-top: 15px;
}
.footer_tag .address{
	width: 50%;
	text-align: right;
	color:#0264c9;
	float: right;
	padding-top: 15px;
}
.header_date{
	width:20%;
	float: left; 
	color:#0264c9;
	padding-top: 15px;
	vertical-align: middle;
}
.header_logo{
	width:20%; 
	float: left;
	text-align: center;
	vertical-align: middle;
	padding:0px 20%; 
}

.page_number{
	width:20%; 
	float: left;
	text-align: right;
	color:#0264c9;
	padding-top: 15px;
	vertical-align: middle;
}
.heading_list{
	display: table;

   width: 100%;
}


.p_tag{font-size:30px;
	color: #015bba;
	line-height: 0px;
	margin: 0px;
	margin-top:30px;
	margin-bottom:15px;
}


.heading{
	margin: 0px;
	margin: 0px;
	line-height: 0px;
	font-size:36px;
	font-weight: 600;
	text-align: center;
	color: #015bba;
	padding-bottom: 10px;
	padding-top: 30px;
}

.display_table{
	display: block;
    width: 100%;
    float: left;
    background: #fff;
	/*display:flex !important;*/
	width: 100%;
	background:#fff;
	/*display: flex;
	flex-direction: row;
	justify-content: space-between;
	display: -webkit-flex;
	-webkit-flex-direction: row;
	-webkit-justify-content: space-between; */
	margin-top:20px;
}
.text_security{
	    color: rgb(1,91,187);
	    font-size: 24px;
	    padding-top:15px;
}
.products_details{
	width: 49.5%;
	/*display: table-cell;*/
	float: left;
	padding: 0px;
	margin-top:0px;
	margin-bottom: 10px;
	vertical-align: top;
}
.products_id{
	width: 35%;
	font-size: 13px;
	text-align: left;
	color: #015bba;
	float: left;
	margin-top: 0px;
	margin-bottom: 0px;
}

.products_price{
	width: 65%;
	font-size: 12px;
	text-align: left;
	color: #015bba;
	float: left;
	margin-left: 10px;
	margin-top: 0px;
	margin-bottom: 0px;
}




.product_img{
	width: 35%;
	float: left;
	height: auto;
	border: 1px solid #b1b2b4;
	height: 105px;
	padding-top: 15px;
}

.products_det{
	padding:0px 10px;
}
.products_det ul{
	padding-left:12px; 
}
.products_det ul li{
	font-size: 10px;
	padding-left: 7px;
	color: #595b60;
}
.more_info{
	width:65%; margin-left:35%; text-align:left; font-size:10px;
	margin-top:-15px;
	padding-left: 10px; }
.more_info a:hover{
	background: none;
	border: 0px !important;
}
.products_det ul{
	padding-bottom: 10px;
}
.more_info a{
	color: #015bba;
}

p.text{
	font-size: 13px;
	text-align: left;
	color:#595b60;
	padding-bottom:15px;
	margin:0px !important; 
}

.gst{
vertical-align:top; font-size:7px; margin-top:-10px; line-height:0px;
position: absolute;
top: 10;	
}



.item_id{
	text-align: left;
	color: #6c6c70;
	padding-top: 7px;
	padding-bottom: 7px;
	vertical-align: top;
}
.item_gst{
	text-align: right;
	color:#015bba;
	padding-top: 7px;
	padding-bottom: 0px;
	vertical-align: top;
}
.item_gst_code{
	text-align: right;
	color:#015bba;
	padding-top: 7px;
	padding-bottom: 0px;
}
.item_id_title{
	text-align: left;
	color: #6c6c70;
	padding-top: 0px;
	padding-bottom: 7px;

}

.product_img_new{
	height: 150px;
	border:1px solid #b1b2b4;
}
.product_page tr td.top{
	vertical-align: top;
}
.product_page .caption{
	padding-top: 30px;
	padding-bottom: 0px;
	text-align: left;
	color:#015bba;
}
.gradient{ float:left; width: 50%;}
.gradient_1{
	float:right;
	width: 50%;
}
.img_and_pro_details{
	padding-top: 7px;
}
.head_text{
	font-size: 36px;
	text-align: center;
	border-bottom: 1px solid #73a4d9;
	color: #015bba;
	padding-bottom: 5px;
}
.dottab_color{
	color: #73a4d9;
}
.title-text{
	color: #015bba;
	font-size: 20px;
}</style>

<p class="head_text"><b>West Side Security Services</b></p>

					<p><b class="p_tag">Access:</b><span class="dottab_color"><dottab /></span><b class="p_tag">01</b></p>			
					<p><b class="p_tag">Audio:</b><span class="dottab_color"><dottab /></span><b class="p_tag">01</b></p>			
					<p><b class="p_tag">Cable:</b><span class="dottab_color"><dottab /></span><b class="p_tag">01</b></p>			
					<p><b class="p_tag">Evac:</b><span class="dottab_color"><dottab /></span><b class="p_tag">01</b></p>				
					<p><b class="p_tag">Intercoms:</b><span class="dottab_color"><dottab /></span><b class="p_tag">01</b></p>			
					<p><b class="p_tag">Intrusion:</b><span class="dottab_color"><dottab /></span><b class="p_tag">01</b></p>			
					<p><b class="p_tag">Network:</b><span class="dottab_color"><dottab /></span><b class="p_tag">01</b></p>			
					<p><b class="p_tag">Power:</b><span class="dottab_color"><dottab /></span><b class="p_tag">01</b></p>				
					<p><b class="p_tag">Structured Cabling:</b><span class="dottab_color"><dottab /></span><b class="p_tag">01</b></p>			
					<p><b class="p_tag">Tools & Hardware:</b><span class="dottab_color"><dottab /></span><b class="p_tag">01</b></p>			
					<p><b class="p_tag">Vacuums:</b><span class="dottab_color"><dottab /></span><b class="p_tag">01</b></p>			
					<p><b class="p_tag">Video:</b><span class="dottab_color"><dottab /></span><b class="p_tag">01</b></p>




<div class="display_table">

<p class="head_text"><b>Cable</b></p>

	<p class="title-text"><b>Security:</b></p>
	<div style="width:100%;float:left;">
	<div class="products_details">
		<div class="header_div">
			<div class="products_id"><b>51026RH:</b></div>
			<div class="products_price"><b>$31.75</b><sup>+gst</sup></div>
		</div>
		<div class="img_and_pro_details">
			<div class="product_img">
				<img src="images/default_apol_logo.png" />
			</div>
			<div class="products_det">
				<ul>
					<li>14.020 4 Core Cable 100 Metres</li>
					<li>The anodized aluminum rain hood is fit for
						both surface mounting (by screwing into the
						flush box) and flush mounting (supporting tool
						kit is provided in the package)
					</li>
				</ul>
			</div>
			<p class="more_info"><a href="https://apol.com.au/apol-html/"><b>VIEW PRODUCT ONLINE</b></a></p>
			<p class="text"><b>6 Core Security Cable (Heavy)</b></p>
		</div>
	</div>

	<div class="products_details">
		<div class="header_div">
			<div class="products_id"><b>51026RH:</b></div>
			<div class="products_price"><b>$31.75</b><sup>+gst</sup></div>
		</div>
		<div class="img_and_pro_details">
			<div class="product_img">
				<img src="images/default_apol_logo.png" />
			</div>
			<div class="products_det">
				<ul>
					<li>14.020 4 Core Cable 100 Metres</li>
					<li>The anodized aluminum rain hood is fit for
						both surface mounting (by screwing into the
						flush box) and flush mounting (supporting tool
						kit is provided in the package)
					</li>
				</ul>
			</div>
			<p class="more_info"><a href="https://apol.com.au/apol-html/"><b>VIEW PRODUCT ONLINE</b></a></p>
			<p class="text"><b>6 Core Security Cable (Heavy)</b></p>
		</div>
	</div>
</div>
	</div></div>';

echo $html; die();
include("mpdf60/mpdf.php");
ob_clean(); // cleaning the buffer before Output()

//$mpdf=new mPDF(); 
$mpdf=new mPDF("en-GB-x", "A4"); 
$mpdf=new mPDF('en-GB-x','A4','','',15,15,30,0,10,10);/*left,right,top*/
$stylesheet = file_get_contents('pdf.css');
$mpdf->WriteHTML($stylesheet,1);
$mpdf->defaultheaderline = 0; /* 1 to include line below header/above footer */


// $mpdf->SetHTMLFooter('', 'O');
//$mpdf->setFooter('{PAGENO}');
$mpdf->SetHTMLHeader('
<div class="header">
	<div class="header_date">{DATE j-m-Y}</div>
	<div class="header_logo"><img src="images/default_apol_logo.png"></div>
	<div class="page_number">{PAGENO}/2</div>
</div>
', 'O');

$mpdf->SetHTMLFooter('
 <div class="footer_tag">
 	<div class="calling">Call: 1300 135 905</div>
 	<div class="address">68 Bell St, Heidelberg Heights, VIC</div>
 	<div class="West Side Security Services"></div>
 </div>
', 'O' );


//$mpdf->setFooter('{PAGENO}')

$mpdf->WriteHTML($html);
$mpdf->Output();
$mpdf->AddPage('', // L - landscape, P - portrait 
        '', '', '', '',
        5, // margin_left
        5, // margin right
       150, // margin top
       30, // margin bottom
        0, // margin header
        0); // margin footer
?>


