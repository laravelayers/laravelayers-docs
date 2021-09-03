@push('scripts')

    <script>

        window.onload = function(){
            document.body.addEventListener('dblclick', function(e){
                var target = e.target || e.srcElement;
                if (target.className.indexOf("hljs") !== -1 || target.parentNode.className.indexOf("hljs") !== -1){
                    var range, selection;

                    if (document.body.createTextRange) {
                        range = document.body.createTextRange();
                        range.moveToElementText(target);
                        range.select();
                    } else if (window.getSelection) {
                        selection = window.getSelection();
                        range = document.createRange();
                        range.selectNodeContents(target);
                        selection.removeAllRanges();
                        selection.addRange(range);
                    }
                    e.stopPropagation();
                }
            });
        };

    </script>

@endpush
