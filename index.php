<html>
<title>aceList: Free PHP/SQLite based subscription system</title>
<link rel="stylesheet" href="aceList/style.css">
<!-- CDN? https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js -->
<script src="aceList/jquery-3.1.1.min.js"></script>
<script src="aceList/app.js"></script>
<body>
<div id="aceList">
    <div class="acelist_inn">
        <div class="acelist_logo_cont">
            <img src="aceList/aceList.png" alt="aceList" class="acelist_logo"/>
        </div>
        <!-- Everything inside #aceList-replace will be replaced in other pages -->
        <div id="aceList-replace">
        <div class="ace-head">
        </div>
        <p class="acelist_info">
            Hello, I'll let you know whenever I release some new code.
        </p>
        <form action="" method="POST" id="aceListForm">

                <div id="aceList-response" class="animated bounceIn"></div>

                <div class="acelist-input-wrapper acelist-half">
                    <label for="firstName">First Name <i class="required">*</i></label>
                    <input name="firstName" autofocus type="text" class="acelist_inp" data-required="1" />
                </div>

                <div class="acelist-input-wrapper acelist-half">
                    <label for="lastName">Last Name</label>
                    <input name="lastName" type="text" class="acelist_inp"  />
                </div>

                <div class="last acelist-input-wrapper acelist-block">
                    <label for="email">Email Address <i class="required">*</i></label>
                    <input name="email" type="email" class="acelist_inp" data-required="1" />
                </div>

                <!-- Date/Time would be injected by JS for client timestamp with Timezone -->
                <input type="text" name="dateTime" value="" class="acelist_hide"/>

                <input type="submit" class="acelist_btn" value="I want in!" />

            </form>
        <p class="acelist_foot red">PS: This is a real form. Please do not enter fake email IDs.</p>
        </div>
    </div>
</div>
</body>
</html>