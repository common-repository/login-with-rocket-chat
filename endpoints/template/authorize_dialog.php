<?php
function rocketchat_server_emit_css() {
    ?>
    <style>
        body {
            color: #212121;
        }
        .grant-container {
            background: #aaaaaa55;
            margin: 0;
            padding: 10px;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
        }
        .grant-dialog {
            background: #ffffff;
            z-index: 1;
            border: 1px solid #eee;
            width: 35%;
            margin: 0 auto;
            position: absolute;
            left: 50%;
            top: 50%;
            padding: 1.5em;
            transform: translate(-50%, -50%);
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.6);
            transition: 0.3s;
        }
        .grant-dialog:hover {
            box-shadow: 0 8px 16px 0 rgba(0,0,0,0.8);
        }
        .grant-dialog hr {
            border: 1px solid #848484;
        }
        .dialog-header {
            color: #424242;
        }
        .mo-current-consent {
            padding: 2%;
            background: #7ed3d344;
            border: 1px solid #79cece;
            border-radius: 5px;
            text-align: center;
        }

        .grant-dialog input[type="submit"] {
            border: none;
            outline: none;
            width: 100%;
            display: block;
            text-align: center;
            text-transform: uppercase;
            padding: 1em;
            font-size: 14px;
            cursor: pointer;
            transition: 0.3s ease all;
            letter-spacing: .1rem;
            border-radius: 5px;
        }
        .grant-dialog ul {
            list-style-type: none;
            list-style-position: inside;
            text-indent: -1.5em;
        }
        .mo-consent-list > li:before {
            content: '\2705';
            /* margin-right: 0.5rem; */
            padding: .5rem;
            border-top: 1px solid #eee;
        }
        .mo-consent-list > li:before:last-child {
            border-bottom: 1px solid #eee;
        }
        .mo-consent-list > li {
            padding: .5rem;
            border-top: 1px solid #eee;
        }
        .mo-consent-list > li:last-child {
            border-bottom: 1px solid #eee;
        }
        .grant-allow {
            background-color: #28a745dd;
            color: #fff;
        }
        .grant-allow:hover {
            background-color: #28a745;
        }
        .grant-deny {
            background-color: #dc3545dd;
            color: #fff;
        }
        .grant-deny:hover {
            background-color: #dc3545;
        }
        @media(max-width: 992px) {
            .grant-dialog {
                width: 50%;
                left: 50%;
                top: 10%;
                padding: 1em;
                transform: translate(-50%);
            }
            .mo-dialog-title {
                font-size: 1.2rem !important;
            }
        }
        .mo-dialog-title {
            font-weight: 300;
            font-size: 1.5rem;
            color: #424242;
        }
    </style>
    <?php
}

function rocketchat_mo_server_emit_html( $client_credentials ) {
    rocketchat_server_emit_css();
    ?>
        <div class="grant-container">
            <div class="grant-dialog">
                <h2 class="dialog-header">Authorize</h2>
                <hr align="left" width="10%" />
                <p class="mo-dialog-title">The application '<?php echo $client_credentials['client_name']; ?>' wants to access following information:</p>
                <ul class="mo-consent-list">
                    <li>Basic pubilc profile</li>
                    <li>Public email</li>
                </ul>
                <p class="mo-current-consent"><small>This application cannot continue if you do not allow this application.</small></p>
                <form action="" method="post">
                    <?php wp_nonce_field( 'mo-oauth-server-authorize-dialog', 'nonce' ); ?>
                    <input type="hidden" name="authorize-dialog" value="1"/>
                    <input type="hidden" name="authorize" value="allow"/>
                    <input class="grant-allow" type="submit" value="Allow"/>
                </form>

                <form action="" method="post">
                    <?php wp_nonce_field( 'mo-oauth-server-authorize-dialog', 'nonce' ); ?>
                    <input type="hidden" name="authorize-dialog" value="1"/>
                    <input type="hidden" name="authorize" value="deny"/>
                    <input class="grant-deny" type="submit" value="Deny"/>
                </form>
            </div>
        </div>
    <?php
}
