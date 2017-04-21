(function () {

    jQuery(document).ready(function ($) {

        // define any global vars within this scope
        var my_var;

        function init_stats() {
            var status = $('#cron-status');
            status.html('loading..');
        }

        function update_status(res) {

            var status = $('#cron-status');
            status.html('');

            status.append('<p class="cron-status ' + res.data.status + '">' + res.data.status + '</p>');
            if (res.data.started) {
                status.append('<p class="cron-started">' + res.data.started + '</p>');
            }
            if (res.data.processed_jobs) {
                status.append('<p class="cron-processed-jobs">' + res.data.processed_jobs + '</p>');
            }
            if (res.data.memory) {
                status.append('<p class="cron-memory">' + res.data.memory + '</p>');
            }

        }

        // create global functions within this scope
        function init_cron_master() {
            init_stats();
            $.ajax({
                url: ajaxurl,
                type: 'GET',
                cache: false,
                data: {
                    action: 'init_cron_master'
                },
            }).success(function (res) {
                update_status(res);
            });
        }

        // create global functions within this scope
        function clear_logs() {

            $.ajax({
                url: ajaxurl,
                type: 'GET',
                cache: false,
                data: {
                    action: 'clear_logs_cron_master'
                },
            }).success(function (res) {

            });
        }

        function read_runner_status() {

            $.ajax({
                url: ajaxurl,
                type: 'GET',
                cache: false,
                data: {
                    action: 'read_cron_master'
                },
            }).success(function (res) {
                update_status(res);
            });
        }

        function stop_runner() {
            init_stats();
            $.ajax({
                url: ajaxurl,
                type: 'GET',
                cache: false,
                data: {
                    action: 'stop_cron_master'
                },
            }).success(function (res) {
                update_status(res)
            });
        }

        // initilize instance
        $(document).on('cron_master.init', function () {

            // init instance vars
            var my_var;

            // create private instance functions
            function my_private_function() {
            }

            // create instance bindings
            $(this).on('event', '.selector', callback);

        });

        // setup global bindings
        $(window).ready(init_cron_master);

        $(document).on('click', '.start-cron', init_cron_master);
        $(document).on('click', '.get-status', function () {
            init_stats();
            read_runner_status();
        });
        $(document).on('click', '.stop-cron', stop_runner);
        $(document).on('click', '.clear-logs', clear_logs);

        setInterval(read_runner_status, 30000);

    });

})(window);