<?php
if (!defined('ABSPATH')) { exit; }

/**
 * Source-of-truth data for the 16 owned Mentra articles migrated in Phase 4.
 * Dates are the original publish timestamps (UTC) captured from the source
 * <time datetime="..."> attributes. Bodies are full Vietnamese translations
 * of the English source content in
 * wp-content/themes/mentra-vietnam/templates/source/blogs/blog/*.html -
 * see docs/news-migration-manifest.md for the verification table.
 * Image paths are relative to the theme's assets/ directory (matching
 * mentra_vn_asset()); most live under assets/news/, two live at assets/ root.
 */
function mentra_vn_articles() {
    return [
        [
            'slug' => 'mentra-3-0-local-miniapps-full-user-control-and-enterprise-smart-glasses',
            'title' => 'Mentra 3.0 - Miniapp cục bộ, toàn quyền kiểm soát và kính thông minh cho doanh nghiệp',
            'date' => '2026-08-17 09:06:59',
            'excerpt' => 'Mentra 3.0 xây dựng lại MentraOS xoay quanh các miniapp cục bộ nhanh và ổn định. Bản cập nhật mang đến nhiều cải tiến lớn cho Mentra Live, SDK Miniapp mới và thư viện công cụ ngày càng phong phú, đồng thời đặt nền móng cho kính thông minh doanh nghiệp an toàn và mở.',
            'image' => 'Mockup_OS_Phone_Image2.png',
            'authors' => [['name' => 'Cayden Pierce', 'role' => 'CEO', 'avatar' => 'news/Cayden_Headshot.png']],
            'body' => <<<'HTML'
<p>Gần hai năm trước, chúng tôi ra mắt MentraOS với một mục tiêu duy nhất: xây dựng một hệ điều hành mở, do người dùng kiểm soát và có thể dùng chung cho mọi loại kính thông minh. Một nơi để người dùng khám phá miniapp, một nền tảng để nhà phát triển xây dựng trải nghiệm hoạt động trên nhiều loại kính khác nhau, một hệ thống để các hãng OEM trang bị cho thế hệ kính thông minh tiếp theo của họ.</p>
<p>MentraOS lần đầu đến với người dùng qua Vuzix Z100 và Even Realities G1. Những người dùng đầu tiên đó đã cho chúng tôi biết mọi người thực sự cần gì ở kính thông minh: miniapp và SDK đủ tin cậy để dùng mỗi ngày.</p>
<p>Mentra Live mở rộng tầm nhìn đó với camera, microphone và một loạt khả năng hoàn toàn mới. Nó cũng cho chúng tôi thấy trải nghiệm vẫn cần cải thiện ở đâu. Kể từ khi ra mắt, chúng tôi đã lắng nghe sát sao người dùng và tập trung làm cho các tính năng cốt lõi nhanh hơn, dễ dùng hơn và đáng tin cậy hơn nhiều.</p>
<p>Chúng tôi cũng nhận ra rằng chạy miniapp trên cloud không phải nền tảng đúng đắn về lâu dài. Cách này giúp chúng tôi triển khai nhanh, nhưng lại gây ra độ trễ, hạn chế về độ tin cậy, quyền riêng tư và khả năng hoạt động offline mà người dùng, nhà phát triển và các hãng OEM đều cảm nhận được.</p>
<p>Mentra 3.0 là câu trả lời của chúng tôi cho tất cả những gì đã học được. Chúng tôi đã xây dựng lại phần lớn MentraOS xoay quanh các miniapp chạy cục bộ ngay trên điện thoại, giới thiệu Mentra Miniapp SDK hoàn toàn mới, cải thiện đáng kể Mentra Live, và tạo ra một thư viện miniapp mới.</p>
<p>Hôm nay, chúng tôi ra mắt Mentra 3.0 - bản cập nhật lớn nhất từ trước đến nay của MentraOS.</p>
<h3>Nhật ký thay đổi của MentraOS</h3>
<ul>
<li>MentraOS đã được viết lại gần như hoàn toàn.</li>
<li>Tất cả miniapp giờ chạy hoàn toàn trên điện thoại.</li>
<li>Tất cả miniapp giờ đáng tin cậy hơn nhiều và phản hồi thao tác người dùng nhanh hơn.</li>
<li>Menu dạng viên nang (capsule menu) bên trong miniapp đã được thiết kế lại, giờ có thêm tùy chọn thoát hoặc thu nhỏ miniapp.</li>
<li>Có một <a href="https://docs.mentra.glass/">Miniapp SDK</a> hoàn toàn mới, giúp việc xây dựng miniapp cho mọi loại kính chạy MentraOS trở nên cực kỳ dễ dàng.</li>
<li>Miniapp giờ có thể expose các hành động (actions) để miniapp khác, như Mentra AI, có thể kích hoạt.</li>
<li>Trang cài đặt giờ dễ điều hướng hơn.</li>
<li>Mô hình speech-to-text và text-to-speech chạy cục bộ giờ tự động tải về qua Wi-Fi, và miniapp sẽ tự động dùng chúng khi mất kết nối internet.</li>
</ul>
<h3>Nhật ký thay đổi của Mentra Live</h3>
<p>Chúng tôi đã dành trọn tháng vừa qua tập trung hoàn toàn vào việc cải thiện Mentra Live.</p>
<ul>
<li>Chụp ảnh nhanh hơn đáng kể, đặc biệt với các lượt chụp liên tiếp và ảnh do miniapp yêu cầu.</li>
<li>Ảnh truyền về điện thoại qua Bluetooth giờ có độ phân giải cao hơn nhiều, đến nhanh hơn và giữ được nhiều chi tiết hơn.</li>
<li>Đồng bộ ảnh và video từ Mentra Live không còn làm gián đoạn kết nối internet trên điện thoại.</li>
<li>Đồng bộ thư viện ảnh giờ có thể tiếp tục và khôi phục lại. Các lượt truyền bị gián đoạn sẽ tự động tiếp tục, các mục thiếu trong thư viện sẽ được khôi phục, và ảnh đã tải về ứng dụng Mentra sẽ được đối chiếu an toàn với camera roll của bạn.</li>
<li>Chúng tôi đã thêm các tùy chỉnh camera mới, gồm quay ở 5, 15 hoặc 30 khung hình/giây, và dải góc nhìn (FOV) camera rộng hơn.</li>
<li>Livestream giờ đáng tin cậy hơn và tự động khôi phục sau khi mất kết nối mạng tạm thời. Mentra Live giờ dùng mã hóa video tăng tốc phần cứng khi khả dụng, giúp giảm hao pin và nhiệt.</li>
<li>Thiết lập Wi-Fi đáng tin cậy hơn.</li>
<li>Miniapp giờ có thể làm nóng trước (pre-warm) camera của Mentra Live để chụp gần như tức thì, quay video, chọn độ phân giải và khung hình, và yêu cầu chế độ chụp mới được tối ưu cho việc đọc văn bản.</li>
</ul>
<h3>Nhật ký thay đổi của Even Realities G2</h3>
<p>Chúng tôi đã lắng nghe người dùng Even Realities G2! Giờ đây double-tap sẽ mở dashboard gốc của G2, giúp chuyển miniapp ngay từ kính dễ dàng hơn và nhận thông báo từ iPhone.</p>
<p>Chúng tôi cũng đã cải tiến khả năng khôi phục màn hình và microphone của G2. Phụ đề và Dịch vẫn phản hồi tốt sau khi mở hoặc đóng dashboard, văn bản và hình ảnh hiển thị ổn định hơn, và đồng bộ lịch cũng được cải thiện.</p>
<h3>Kho Miniapp</h3>
<p>Mentra Miniapp Store vẫn là một phần nền tảng cốt lõi trong hệ sinh thái của chúng tôi. Chúng tôi đang cải tiến Mentra Miniapp Store để hoạt động cùng hệ thống miniapp cục bộ mới. Chúng tôi thiết kế nó theo cách để các hãng kính thông minh khác cũng có thể sử dụng kho ứng dụng này, để nó không chỉ truy cập được qua ứng dụng Mentra. Vì vậy, chúng tôi vẫn còn việc phải làm với Miniapp Store. Do đó, bản phát hành đầu tiên của Mentra 3.0 chưa bao gồm Miniapp Store. Thay vào đó, tất cả miniapp chính thức của Mentra sẽ được cài đặt sẵn theo mặc định khi cài Mentra 3.0. Miniapp Store sẽ quay lại vào cuối năm 2026.</p>
<h3>Miniapp Merge</h3>
<p>Chúng tôi đã viết lại hoàn toàn Mentra Merge - trợ lý AI chủ động của chúng tôi - từ đầu.</p>
<p>Merge giờ thông minh hơn, có giao diện hoàn toàn mới trên điện thoại, và giờ đã tương thích với Mentra Live.</p>
<h3>Miniapp Phụ đề</h3>
<p>Miniapp Phụ đề giờ tự động chuyển đổi giữa chế độ phiên dịch online và offline.</p>
<h3>Miniapp Dịch</h3>
<p>Tính năng Dịch đã được viết lại hoàn toàn. Giờ đây nó tự động nhận diện ngôn ngữ nước ngoài, nên người dùng không cần chọn ngôn ngữ theo cách thủ công nữa. Nó cũng có thể dịch nhiều ngôn ngữ cùng lúc.</p>
<p>Tiếng Việt giờ đã được hỗ trợ trên Even Realities G2.</p>
<h3>Mentra Notes</h3>
<p>Chúng tôi đã giúp việc xuất ghi chú và bản ghi từ Mentra Notes dễ dàng hơn. Nhấn nút "xuất", bạn sẽ có tùy chọn xuất sang bất kỳ ứng dụng nào trên điện thoại.</p>
<h3>Miniapp Mentra AI</h3>
<p>Mentra AI giờ thông minh hơn và có quyền truy cập vào các công cụ mới. Chúng tôi đã thêm bộ chọn model để bạn có thể dùng bất kỳ model AI nào mình muốn, và cho phép nó khởi động, dừng và kích hoạt các hành động trong miniapp khác. Nếu bạn là nhà phát triển miniapp, bạn có thể chỉ định các hành động trong chính miniapp của mình mà Mentra AI có thể truy cập.</p>
<h3>Miniapp Mentra Maps</h3>
<p>Giới thiệu Mentra Maps - một miniapp điều hướng hoàn toàn mới, ra mắt lần đầu cùng Mentra 3.0.</p>
<p>Mentra Maps cung cấp chỉ dẫn từng chặng bằng hình ảnh và giọng nói ngay trên kính thông minh của bạn. Nó khả dụng trên mọi loại kính tương thích MentraOS, và chúng tôi rất mong nhận được phản hồi từ bạn.</p>
<h3>Miniapp Teleprompter</h3>
<p>Teleprompter đã trở lại và được xây dựng lại hoàn toàn cho Mentra Miniapp SDK mới.</p>
<p>Chúng tôi đã ngừng phiên bản cũ vì nó không còn đạt chuẩn chất lượng của chúng tôi. Teleprompter mới chạy hoàn toàn trên điện thoại, hoạt động không cần kết nối internet, và có cả chế độ cuộn tự động lẫn chế độ AI Scroll bám theo giọng nói của bạn.</p>
<h3>Miniapp Recorder</h3>
<p>Recorder ban đầu chỉ là một công cụ debug nội bộ đơn giản, chưa từng có ý định phát hành công khai. Với Mentra 3.0, chúng tôi đã biến nó thành một miniapp hoàn chỉnh để ghi âm từ kính thông minh của bạn.</p>
<p>Recorder có tính năng tạm dừng và tiếp tục, phiên dịch trực tiếp, phát lại, tìm kiếm bản ghi, và khả năng chia sẻ audio hoặc bản ghi văn bản. Nó tương thích với mọi loại kính chạy MentraOS.</p>
<h3>Kết luận và những gì sắp tới - Kính thông minh cho doanh nghiệp</h3>
<p>Mentra mới chỉ bắt đầu. Sau khi có hàng chục nghìn chiếc kính kết nối với MentraOS, chúng tôi đã học được rất nhiều về những gì hiệu quả, những gì chưa, và những gì sắp tới. Xuyên suốt hành trình đó, chúng tôi vẫn giữ vững tầm nhìn xây dựng hệ điều hành mở cho kính thông minh, trao toàn quyền kiểm soát cho người dùng. Và chúng tôi tin điều đó bắt đầu từ doanh nghiệp.</p>
<p>Kể từ khi ra mắt Mentra Live, chúng tôi đã giao hàng nghìn thiết bị. Chúng tôi đã chứng kiến nhà phát triển, người dùng cá nhân và doanh nghiệp sử dụng Mentra Live cho hàng chục trường hợp sử dụng khác nhau. Chúng tôi cũng chứng kiến các công ty như Even Realities, Meta, Rokid và nhiều hãng khác ra mắt tầm nhìn riêng của họ về kính thông minh.<br/><br/>Dữ liệu từ góc nhìn của chúng tôi rất rõ ràng. Nhu cầu lớn nhất với kính thông minh hiện nay nằm ở khối doanh nghiệp. Trong khi AI đã chiếm lĩnh thế giới công việc tri thức, nó gần như chưa chạm tới thế giới vật lý. Vậy mà hơn 70% nền kinh tế thế giới vẫn vận hành bằng tay chân. Kính thông minh cho phép AI nhìn thấy những gì người lao động nhìn thấy, nghe thấy những gì họ nghe thấy, và hướng dẫn họ ngay tại thời điểm đó. Khả năng ghi lại, đọc, tra cứu, hướng dẫn, ghi chú ngược lại và xác minh chính là vòng lặp mà kính thông minh mang lại cho ngành sửa chữa ô tô, sản xuất, hậu cần, HVAC và mọi loại công việc tuyến đầu.</p>
<p>MentraOS là hệ thống kính thông minh duy nhất hiện nay cho phép điều đó xảy ra. Nó là mã nguồn mở và do người dùng kiểm soát nên các doanh nghiệp có thể tin tưởng. Nó cho phép doanh nghiệp sở hữu dữ liệu của chính mình, dùng AI của riêng mình, và triển khai ở chế độ air-gapped (cách ly mạng). Chúng tôi đang nâng cấp MentraOS để đáp ứng nhu cầu của doanh nghiệp về bảo mật dữ liệu, quyền riêng tư, air-gapping, triển khai, quản lý đội thiết bị và nhiều hơn nữa. Hỗ trợ chuyên gia từ xa, tự động lập tài liệu, và trợ lý AI thị giác rảnh tay. Đây là cách kính thông minh mang lại giá trị ngay hôm nay, và cũng là cách chúng tôi xây dựng hệ điều hành sẽ vận hành mọi chiếc kính thông minh trong tương lai. Và chúng tôi mới chỉ bắt đầu.</p>
<p>– Đội ngũ Mentra</p>
HTML,
        ],
        [
            'slug' => '1-year-of-mentra',
            'title' => 'Một năm của Mentra',
            'date' => '2025-11-30 22:43:25',
            'excerpt' => 'Nhìn lại năm đầu tiên của Mentra: từ những ngày đầu ở Thâm Quyến đến đội ngũ 16 người và hai đợt ra mắt sản phẩm lớn sắp tới.',
            'image' => 'news/1_year_anniversary_at_Mentra_post_Nov_30_2025_new_7f14de88-c1d5-4613-b26d-2182002270f3.png',
            'authors' => [['name' => 'Cayden Pierce', 'role' => 'CEO', 'avatar' => 'news/Cayden_Headshot.png']],
            'body' => <<<'HTML'
<p>Tính đến ngày 25 tháng 11, đã tròn một năm kể từ khi chúng tôi thành lập Mentra. Chúng tôi đang đứng trước thềm hai đợt ra mắt sản phẩm sẽ thay đổi mọi thứ đối với chúng tôi. Chúng tôi hy vọng điều đó cũng sẽ thay đổi mọi thứ với rất nhiều bạn.</p>
<h2>Vì sao chúng tôi xây dựng Mentra</h2>
<p>500 triệu năm trước, chúng ta còn là những sinh vật đơn giản hơn nhiều, gần như hoàn toàn dựa vào bản năng và vận hành bằng lớp não cổ xưa nhất. Sau đó, khoảng 50 triệu năm trước, quá trình tiến hóa tăng tốc khi vỏ não mới (neocortex) hình thành bao quanh mạch não cổ xưa đó, tạo nên một lớp xử lý thứ hai. Rồi chỉ 5.000 năm trước, ngoại vỏ não (exocortex) xuất hiện như lớp xử lý công nghệ thứ ba của chúng ta. Chúng ta tiếp cận lớp mới này qua các giao diện như bút, giấy, bàn phím và màn hình - những công cụ mở rộng khả năng giao tiếp, ghi nhớ và tư duy của con người. Giờ đây, kính thông minh đang trở thành giao diện tiếp theo, đưa ngoại vỏ não vào thẳng tầm nhìn của chúng ta.</p>
<p>Điện thoại di động hiện vẫn là giao diện phổ biến nhất. Chúng ta lấy điện thoại ra khỏi túi 200 lần mỗi ngày chỉ để đưa màn hình vào trước mắt và loa lại gần tai. Nhưng kính thông minh sắp vươn lên dẫn đầu vì chúng tốt hơn điện thoại di động.</p>
<p>Chúng tốt hơn vì rảnh tay, nên bạn có thể quay video khi đang bế con hoặc xem bản dịch trực tiếp khi đang ăn, mà không cần cầm điện thoại.</p>
<p>Chúng tốt hơn vì hiển thị ngay trước mắt (heads-up), nên bạn có thể đọc phụ đề khi đang trò chuyện với bạn bè hoặc tìm đường ở một thành phố mới mà vẫn tận hưởng khung cảnh xung quanh.</p>
<p>Chúng tốt hơn vì nắm bắt được ngữ cảnh, nên khi ở cửa hàng bạn có thể hỏi AI của mình "vợ tôi nhờ mua gì nhỉ?" hoặc lưu lại ghi chú từ các cuộc họp ngoài đời thực.<br/><br/>Chúng tốt hơn vì kết hợp được thế giới số và thế giới vật lý. Kính thông minh có thể phủ lên bất cứ thứ gì bạn cần ngay trên môi trường xung quanh: bản đồ 3D tại trung tâm thương mại, đánh giá nhà hàng khi bạn đi ngang qua, lịch trình xe buýt trực tiếp, tin nhắn không gian như những tờ ghi chú số, mô hình CAD 3D, hay xem trước nội thất mới ngay trong phòng khách của bạn.</p>
<p>Một trong những nền tảng quan trọng nhất từng phát triển chính là Internet. Một mạng lưới các giao thức mở, tương thích lẫn nhau đã cùng nhau tạo nên điều kỳ diệu. Giờ đây, những con người và công nghệ khác nhau có thể giao tiếp và cùng làm việc với nhau. Nhưng trong 20 năm qua, các hãng công nghệ lớn đã chia Internet thành những vùng đóng kín, không tương thích với nhau. Cùng lúc đó, Android và iOS đã hạn chế mạnh mẽ những gì ứng dụng bên thứ ba có thể làm, khi Android ngày càng khép kín và kiểm soát chặt hơn. Tương lai tuyệt vời mà chúng ta từng hình dung đang bị đánh cắp vì máy tính, phần mềm và mạng lưới trở thành những hệ sinh thái không tương thích và đóng kín. Google và Apple không cho phép các ứng dụng cạnh tranh như trợ lý AI tồn tại. Việc hạn chế cạnh tranh này làm chậm đổi mới và bó hẹp quyền tự do của người dùng.</p>
<p>Chúng tôi không muốn một tương lai đóng kín, mã nguồn đóng, khu vườn có tường bao và API bị giới hạn. Chúng tôi muốn chiếc máy tính cá nhân tiếp theo giống như những ngày đầu của Internet: mở, kết nối và tương thích lẫn nhau. Đó là lý do Mentra ra đời.</p>
<p>Nhưng kính thông minh ở đâu? Giờ đây việc chế tạo kính thông minh cho người tiêu dùng mới trở nên khả thi nhờ công nghệ thu nhỏ waveguide, màn hình và linh kiện âm thanh, nhưng hệ điều hành cho kính thông minh vẫn còn thiếu. Kính thông minh tạo ra những bài toán mới cho một hệ điều hành phải giải quyết, như ứng dụng nên chạy ở đâu khi kính quá hạn chế về năng lượng để chạy tác vụ cục bộ. Làm sao để nhiều ứng dụng/agent có thể chia sẻ cảm biến và giao diện cùng lúc? Làm sao để nhà phát triển chỉ cần xây một ứng dụng mà chạy được trên bất kỳ loại kính thông minh và điện thoại nào? Đây chính là những thách thức mà hệ điều hành cho kính thông minh phải giải quyết, và cũng là những thách thức mà Mentra đang đối mặt.</p>
<p><em>Chúng tôi xây dựng MentraOS vì tin rằng nếu một hệ điều hành mở, chung, do cộng đồng dẫn dắt chiến thắng trong cuộc đua kính thông minh, nó sẽ tối đa hóa tự do của con người. Hãy cùng nhau xây dựng điều này.</em></p>
<h2>Phiên bản tiếp theo của MentraOS</h2>
<div style="text-align: left;"><img alt="Thiết kế giao diện MentraOS 3.0" height="473" src="{{THEME_URI}}/assets/news/MentraOS_3p0_app_design.png" style="float: none; display: block; margin-left: auto; margin-right: auto;" width="631"/></div>
<p>Sau 11 tháng triển khai MentraOS cho hàng nghìn người dùng, chúng tôi nhận ra những ứng dụng nào được dùng nhiều gấp 10 lần so với phần còn lại: phụ đề, dịch thuật và AI chủ động. Đến cuối mùa hè, chúng tôi nhận thấy để làm tốt những ứng dụng đó, và để hoạt động tốt ở những khu vực có kết nối internet yếu hơn, chúng tôi cần thiết kế lại toàn bộ pipeline dữ liệu, nên đã tái cấu trúc MentraOS từ gốc.</p>
<p>MentraOS 3.0 là kết quả của nỗ lực đó. Chúng tôi đã thiết kế lại giao diện để dễ dùng hơn và đẹp hơn, đồng thời thiết kế lại pipeline dữ liệu để giữ cho phụ đề trực tiếp nhanh, chính xác và ổn định trong mọi tình huống - hãy nghĩ đến phụ đề siêu nhanh ngay cả khi đang ở dưới tàu điện ngầm. Chúng tôi muốn nó trở thành cánh cửa để bạn mở khóa trải nghiệm phụ đề, dịch thuật và AI chủ động tốt nhất có thể trên kính. Và nó sẽ ra mắt vào tháng Một.</p>
<h2>Hạ tầng cho người xây dựng</h2>
<p><img alt="" height="504" src="{{THEME_URI}}/assets/news/mentra_Hackathon_Nov_28_2025_1_33655b7c-d86a-403e-8f0e-c296aaa88460.jpg" style="display: block; margin-left: auto; margin-right: auto;" width="675"/></p>
<p>Trong năm qua, chúng tôi đã chứng kiến khát khao xây dựng đáng kinh ngạc của thị trường. Rất nhiều người có năng lực cao muốn xây dựng trên nền kính thông minh, nhưng họ cần hạ tầng để làm điều đó, và MentraOS chính là hạ tầng ấy. Thị trường đang nhận ra MentraOS là cách để xây dựng ứng dụng kính thông minh, và thở phào nhẹ nhõm vì cuối cùng cũng có một con đường để làm điều đó.</p>
<p>Với Mentra Live, cảm giác đó càng nhân đôi nhờ phần cứng đi kèm - chúng tôi đang mở khóa những điều mà chưa chiếc kính thông minh nào từng làm được. Rất nhiều người có năng lực từng nhìn thấy tiềm năng của Meta Ray-Ban AI Glasses nhưng rồi không thể hiện thực hóa ý tưởng vì không có SDK. Giờ thì không còn nữa, nhờ có Mentra Live. Hãy tưởng tượng kính giúp người khiếm thị "nhìn thấy", giúp tài xế giao hàng ghi lại quá trình giao hàng, và giúp các nhà lãnh đạo tự động ghi chú trong cuộc họp.</p>
<p>Đã đến lúc giải phóng thị trường để cùng nhau xây dựng chiếc máy tính cá nhân tiếp theo.</p>
<h2>Dòng thời gian của Mentra</h2>
<div style="text-align: left;"><img alt="Mentra tại YC gần một năm trước." height="435" src="{{THEME_URI}}/assets/news/YC_Mentra_January2025_cropped.jpg" style="float: none; display: block; margin-left: auto; margin-right: auto;" width="589"/></div>
<p>Chỉ trong 1 năm, chúng tôi đã ra mắt MentraOS cho hàng nghìn người dùng, chế tạo một chiếc kính thông minh vượt trội hơn Meta Ray-Ban (Mentra Live), phát triển đội ngũ lên 16 người tài năng nhất mà tôi từng làm việc cùng, và đang chuẩn bị cho hai đợt ra mắt lớn nhất từ trước đến nay. Khi nhìn lại và tổng hợp dòng thời gian này, tôi thực sự phấn khích với những gì 6 tháng tới sẽ mang lại.</p>
<ul>
<li>Tháng 12/2024: Cayden nghỉ học tại MIT. Được YC chấp nhận. Cả đội sang Thâm Quyến hai tuần để tìm nguồn "compute puck" và phát triển MentraOS.</li>
<li>Tháng 1/2025: YC W25. Cả đội chuyển vào một hacker house cách đó hai dãy nhà. Ra mắt MentraOS 1.0.</li>
<li>Tháng 2/2025: MentraOS 1.0 bộc lộ quá phức tạp và thiếu ổn định, chỉ chạy được trên Android. Đội ngũ bắt đầu viết lại, trở thành MentraOS 2.0.</li>
<li>Tháng 3–4/2025: Gọi vốn vòng hạt giống (seed).</li>
<li>Tháng 5/2025: Dùng vốn gọi được để khởi động chương trình phần cứng kính thông minh của Mentra tại Thâm Quyến.</li>
<li>Tháng 6/2025: Bắt đầu tuyển đội ngũ phần mềm và phần cứng. Nhân sự tăng từ 3 người vào tháng 1 lên 16 người vào tháng 11.</li>
<li>Tháng 7/2025: Ra mắt MentraOS 2.0. Cayden phải rời Mỹ, sang Thâm Quyến và bắt đầu thủ tục visa O-1.</li>
<li>Tháng 8/2025: Ứng dụng bên thứ ba đầu tiên ra mắt trên Mentra Market.</li>
<li>Tháng 9/2025: Cayden nhận được visa O-1 và trở lại Mỹ. Tập trung vào các dự án thí điểm B2B, đạt 50 doanh nghiệp đặt trước Mentra Live.</li>
<li>Tháng 10/2025: Đội ngũ phần mềm xây dựng MentraOS 3.0. Đạt 5.000 nhà phát triển trên Discord.</li>
<li>Tháng 11/2025: Đạt 5 hãng phần cứng kính thông minh chạy MentraOS. Cayden và Carl (Phó chủ tịch mảng Eyewear) sang Thâm Quyến để hoàn thiện Mentra Live cho sản xuất và chuẩn bị mẫu thử đầu tiên cho Mentra Display.</li>
<li>Cuối tháng 11/2025: Khởi động sản xuất 1.000 chiếc đầu tiên.</li>
</ul>
<p>Giờ là lúc để giao hàng. Sáu tháng tới sẽ quyết định tất cả.</p>
<h2>Điều gì tiếp theo</h2>
<p>Trong bài viết tiếp theo, tôi sẽ chia sẻ năm tới sẽ như thế nào đối với Mentra và cộng đồng khi chúng tôi tiếp tục hiện thực hóa tầm nhìn về chiếc máy tính cá nhân tiếp theo cùng nhau. Hãy tham gia phong trào này bằng cách <a href="https://manage.kmail-lists.com/subscriptions/subscribe?a=YAvT3k&amp;g=RZz9s5&amp;utm_source=blog&amp;utm_campaign=1_year_of_mentra_blog_post" rel="noopener" target="_blank">đăng ký nhận bản tin</a> và trở thành một phần của cuộc trò chuyện bằng cách <a href="{{HOME_URL}}/discord/" rel="noopener" target="_blank">tham gia Discord</a>.</p>
HTML,
        ],
        [
            'slug' => 'announcing-mentraos-2-0-and-our-8m-raise',
            'title' => 'Ra mắt MentraOS 2.0 và công bố vòng gọi vốn 8 triệu USD',
            'date' => '2025-07-06 23:00:37',
            'excerpt' => 'MentraOS 2.0 chính thức ra mắt cùng lúc Mentra công bố huy động thành công 8 triệu USD vòng gọi vốn hạt giống để xây dựng hệ điều hành mã nguồn mở cho kính thông minh.',
            'image' => 'news/blog-cover_e2d454c1-36f5-48e0-81ca-8e05a76b1d35.png',
            'authors' => [['name' => 'Cayden Pierce', 'role' => 'CEO', 'avatar' => 'news/Cayden_Headshot.png']],
            'body' => <<<'HTML'
<h2>Ra mắt MentraOS 2.0</h2>
<p>MentraOS (trước đây gọi là AugmentOS) là cách để bạn có được ứng dụng kính thông minh và cũng là cách để xây dựng ứng dụng kính thông minh. Bạn có thể tải về ngay tại <a href="{{HOME_URL}}/mentra-os/">MentraGlass.com/OS</a></p>
<p>Nếu bạn muốn khám phá các ứng dụng khả dụng cho MentraOS, hãy ghé thăm Mentra Store: <a href="https://Apps.MentraGlass.com">Apps.MentraGlass.com</a></p>
<p>Nếu bạn muốn xây dựng ứng dụng kính thông minh bằng SDK kính thông minh mã nguồn mở, hãy xem tài liệu dành cho nhà phát triển: <a href="https://Docs.MentraGlass.com">Docs.MentraGlass.com</a></p>
<h2>Mentra gọi vốn 8 triệu USD</h2>
<p>Cùng lúc đó, chúng tôi công bố đã gọi vốn 8 triệu USD để xây dựng hệ điều hành cho kính thông minh - được hậu thuẫn bởi các nhà sáng lập của Android, YouTube, Pebble, Y Combinator và nhiều hơn nữa (xem hình bên dưới).</p>
<p>Vòng gọi vốn này sẽ giúp chúng tôi xây dựng phần mềm và phần cứng mã nguồn mở cần thiết để mở ra kỷ nguyên của chiếc máy tính cá nhân tiếp theo.</p>
<p>Để biết thêm thông tin, hãy xem các nguồn sau:</p>
<ul>
<li><a href="https://x.com/caydengineer/status/1938667412918018087" rel="noopener" target="_blank">Bài đăng chính thức của Mentra trên X</a></li>
<li><a href="https://www.forbes.com/sites/charliefink/2025/07/01/mentra-raises-8-million-to-launch-open-source-os-for-smart-glasses/">Bài viết về Mentra trên Forbes</a></li>
<li><a href="https://gamesbeat.com/mentra-raises-8m-and-launches-mentraos-2-0-open-source-smartglasses-software/">Bài viết về Mentra trên GamesBeat</a></li>
</ul>
<h2>Các nhà đầu tư của chúng tôi</h2>
<p><img alt="" src="{{THEME_URI}}/assets/news/blog_image.png"/>Chúng tôi tự hào được hậu thuẫn bởi Y Combinator, Rich Miner, Paul Graham, Eric Migicovsky, Jawed Karim, Toyota Ventures, TIRTA Ventures, Amazon Alexa Fund, Hartmann Capital, Alan Rutledge, Kulveer Taggar, D'Arcy Rice, Richard Aberman, Transpose VC, Noah Eisner, Erick Miller, Pioneer Fund, Jason McDowall, Jeff Henrion, Ajira Ventures, Nelson Ray, Echo, Terry + friends, Hat Trick Capital, Chris Smoak, Will Cray, JJ Fliegelman, Mohamed, Brian Li, Yahya Mokhtarzada, Kevin Lin, Nina, Austen Allred, Davin Chin, Olive Tree Capital, Abdul Ly, Multimodal Ventures, 468 Capital, Augustus Odena, Maxwell Nye, Taranjeet Singh, Nate (Roadrunner), NVO Capital, Sajay, Alumni Ventures, Sequoia Scout, Dennis Xu và Ventioneers.<br/><br/>Mentra mới chỉ bắt đầu. Với hàng chục ứng dụng MentraOS mới ra mắt trong mùa hè này và nhiều mẫu kính mới sắp có mặt trong nửa cuối năm 2025, mọi thứ sắp bùng nổ. Cảm ơn bạn đã là một phần của hành trình này.</p>
<p><a href="https://manage.kmail-lists.com/subscriptions/subscribe?a=YAvT3k&amp;g=RZz9s5&amp;utm_source=blog&amp;utm_campaign=announcing_mentraos_2p0_and_8M_blog_post">Đăng ký nhận bản tin</a> để không bỏ lỡ tin tức mới!<br/><br/>Thân mến,<br/>- Đội ngũ Mentra</p>
HTML,
        ],
        [
            'slug' => 'augmentedchords-goes-1-on-hacker-news-sheet-music-app-build-on-mentraos',
            'title' => 'AugmentedChords lên #1 Hacker News - Ứng dụng bản nhạc xây dựng trên MentraOS',
            'date' => '2025-05-06 03:56:00',
            'excerpt' => 'Ứng dụng bản nhạc trên kính thông minh của nhà phát triển cộng đồng Kevin Lin đã leo lên vị trí #1 trên Hacker News, một minh chứng cho sức mạnh của hệ sinh thái MentraOS mở.',
            'image' => 'news/Kevin_Lin_Sheet_Music_Smart_Glasses_May3_2025.jpg',
            'authors' => [['name' => 'Cayden Pierce', 'role' => 'CEO', 'avatar' => 'news/Cayden_Headshot.png']],
            'body' => <<<'HTML'
<p>Kevin Lin đã tham gia hackathon MentraOS đầu tiên của chúng tôi và xây dựng một ứng dụng kính thông minh hiển thị bản nhạc ngay trong tầm nhìn khi anh chơi piano. Sau đó anh đăng video về dự án này và leo lên #1 Hacker News vào ngày 3 tháng 5. Kevin là một huyền thoại trong cộng đồng MentraOS và đã đạt giải ở mọi hackathon của Mentra.<br/><br/>Đây là bài đăng trên Hacker News: <a href="https://news.ycombinator.com/item?id=43906442">https://news.ycombinator.com/item?id=43906442</a></p>
<p>Dưới đây là nguyên văn chia sẻ của Kevin:</p>
<p><br/><em>Xin chào mọi người, tôi là Kevin Lin, và đây là bài Show HN cho dự án kính thông minh hiển thị bản nhạc của tôi. Video của tôi đã lên trang chủ vào thứ Sáu: https://news.ycombinator.com/item?id=43876243, nhưng dang gợi ý chúng tôi nên đăng thêm một bài Show HN, nên đây rồi!</em></p>
<p><em>Tôi đã muốn đưa bản nhạc lên kính thông minh từ lâu, nhưng cơ hội hoàn hảo để thực hiện đến vào giữa tháng Hai, khi Mentra (YC W25) đăng tweet về một hackathon kính thông minh mà họ tổ chức - người thắng cuộc sẽ được mang kính về nhà. Tôi đã tham gia, có những giây phút tuyệt vời khi làm hàng loạt ứng dụng liên quan đến âm nhạc cùng đồng đội, và chúng tôi đã thắng, nên tôi được mang kính về, hoàn thiện dự án, và làm một video khá thú vị về nó (https://www.youtube.com/watch?v=j36u2i7PKKE).</em></p>
<p><em>Chiếc kính là Even Realities G1. Nhìn bề ngoài chúng khá bình thường, nhưng có hai microphone, một màn hình ở mỗi mắt kính, và thậm chí có thể làm theo độ cận. Ai từng thử đeo cũng ngạc nhiên vì màn hình hiển thị tốt đến vậy, và các video quay lại tiếc là chưa lột tả hết được.</em></p>
<p><em>Phần mềm chạy trên AugmentOS - hệ điều hành kính thông minh của Mentra, hoạt động trên nhiều loại kính thông minh của bên thứ ba, bao gồm cả G1. Tất cả những gì tôi cần làm để tạo ứng dụng là viết và chạy một file typescript dùng AugmentOS SDK. Nó cung cấp cho bạn bản ghi giọng nói và âm thanh thô làm đầu vào, cùng văn bản hoặc bitmap làm đầu ra hiển thị trên màn hình, mọi thứ khác đều được trừu tượng hóa hoàn toàn. Kính của bạn giao tiếp với một ứng dụng AugmentOS, rồi ứng dụng đó giao tiếp với dịch vụ typescript của bạn.</em></p>
<p><em>Phần khó duy nhất là viết một script Python để chuyển bản nhạc (định dạng MusicXML) thành các bitmap nhỏ, tối ưu để hiển thị trên màn hình. Ban đầu, hệ sinh thái các thư viện Python liên quan đến âm nhạc hiện có khá thiếu tài liệu và tôi gặp phải nhiều lỗi chưa từng thấy trước đây. Việc thu nhỏ xuống kích thước màn hình bé của kính cũng khiến các thân nốt và dòng kẻ nhạc biến mất, nên tôi nghĩ đến việc dùng phép giãn hình thái học (morphological dilation) để làm nổi bật chúng mà không làm mất khả năng đọc của nốt nhạc. Pipeline cuối cùng là MusicXML → thư viện music21 để render từng đoạn nhịp thành png → giãn ảnh bằng opencv → thu nhỏ → chuyển sang bitmap bằng Pillow → tối ưu bitmap bằng imagemagick. Đây không phải là đoạn code tốt nhất tôi từng viết, nhưng nỗ lực của LLM cho toàn bộ tác vụ này lại khá tệ, và nhiều năm kinh nghiệm Python của tôi thực sự phát huy tác dụng ở đây. Code có trên GitHub: https://github.com/kevinlinxc/AugmentedChords.</em></p>
<p><em>Ghép mọi thứ lại, dịch vụ typescript của tôi sẽ phục vụ các bitmap này cục bộ khi được yêu cầu. Tôi đã làm một giao diện để điều hướng menu và bản nhạc bằng lệnh giọng nói (ví dụ: xem danh mục, tiếp theo, chọn, bắt đầu, thoát, tạm dừng) rồi kết nối bàn đạp chân vào laptop. Vì độ trễ gửi bitmap (~3 giây hiện tại, nhưng các thế hệ kính sau sẽ tốt hơn), dùng bàn đạp chân để lật trang khi đang chơi không khả thi, nên thay vào đó tôi để một bàn đạp bật/tắt tự động cuộn, và hai bàn đạp còn lại tăng tốc/tạm dừng việc cuộn.</em></p>
<p><em>Sau nhiều lần điều chỉnh, tôi đã có thể chơi trọn một bài hát chỉ bằng kính! Phải thử rất nhiều lần và chắc chắn vẫn còn nhiều điều cần cải thiện. Ví dụ: việc gửi bitmap khá chậm, đó là lý do dùng bàn đạp chân để lật trang chưa khả thi; độ phân giải khá nhỏ, tôi muốn hiển thị được nhiều nhịp hơn cùng lúc để lật ít hơn; vì bàn đạp chân không tiện mang theo, sẽ tuyệt nếu có chế độ để âm thanh tự quyết định khi nào bản nhạc chuyển trang - tôi đã thử implement điều đó bằng FFT nhưng thường sai và cần nhiều nỗ lực hơn nữa. Điều khiển bằng nghiêng đầu cũng sẽ hay, vì khả năng điều khiển thủ công hoàn toàn là yêu cầu bắt buộc khi luyện tập.</em></p>
<p><em>Tất cả những điểm hạn chế này đang được Mentra và các công ty khác trong lĩnh vực này giải quyết, nên tôi rất mong chờ thế hệ tiếp theo! Ngoài ra, đừng ngại hỏi tôi bất cứ điều gì!</em></p>
<p>Làm tốt lắm khi xây dựng những ứng dụng thú vị trên MentraOS, Kevin!</p>
HTML,
        ],
        [
            'slug' => 'august-22-community-update',
            'title' => 'Cập nhật cộng đồng - Cải thiện độ ổn định, Mentra Live và màn hình',
            'date' => '2025-08-22 11:05:00',
            'excerpt' => 'Cập nhật nhanh về tiến độ phần mềm, ứng dụng và phần cứng của Mentra, từ cải thiện độ ổn định MentraOS đến tiến độ chế tạo Mentra Live và Nex.',
            'image' => 'news/nex_is_cooking_613cb673-6493-4ecc-ad5d-a2c91ab1cbac.png',
            'authors' => [['name' => 'Cayden Pierce', 'role' => 'CEO', 'avatar' => 'news/Cayden_Headshot.png']],
            'body' => <<<'HTML'
<p>Chào mọi người! Chúng tôi đang chạy hết tốc lực trên phần mềm, ứng dụng và phần cứng. Đây là bản cập nhật nhanh về tình hình hiện tại và định hướng sắp tới của Mentra.</p>
<h2>MentraOS</h2>
<p>Chúng tôi rất phấn khích với tiến độ của Mentra Store. Từ chỗ ra mắt một kho ứng dụng, giờ chúng tôi đã hỗ trợ một bộ 9 ứng dụng. Chúng tôi vẫn tập trung làm tốt những điều cơ bản, nên Mentra sẽ tiếp tục phát triển các ứng dụng nền tảng mà bạn dùng mỗi ngày, đồng thời tạo điều kiện cho cộng đồng nhà phát triển/startup xây dựng đủ loại ứng dụng mới.</p>
<p><img alt="" src="{{THEME_URI}}/assets/news/mentra_store_old.png"/></p>
<p>Vài tuần qua, chúng tôi tập trung vào độ ổn định. Chúng tôi đã lắng nghe phản hồi từ cộng đồng, và đặt độ tin cậy, kiểm thử cùng chu kỳ phát hành phần mềm là ưu tiên hàng đầu. Điều này giúp cải thiện độ ổn định đáng kể - phụ đề giờ "chạy mượt" thật sự. Phiên bản ứng dụng mới sẽ ra mắt trước ngày 29 tháng 8, và chúng tôi sẽ thông báo cho cộng đồng khi nó sẵn sàng.</p>
<p>Tính năng dịch cũng được cải thiện đáng kể với một model mới. Chúng tôi đã dùng nó hằng ngày ở Trung Quốc, và kết quả rất tốt. Hãy thử ngay trên kho ứng dụng!</p>
<p>Mùa thu này, chúng tôi sẽ bổ sung vào kho ứng dụng:</p>
<ul>
<li>Điều hướng - bản đồ ngay trên kính của bạn</li>
<li>Scroller - đọc Reddit, tin tức và các ứng dụng mạng xã hội trên kính thông minh</li>
</ul>
<p>Ứng dụng bên thứ ba sắp ra mắt! Mùa thu này, chúng ta sẽ thấy các ứng dụng bên thứ ba (không phải của Mentra) trên Mentra Store, bao gồm:</p>
<ul>
<li>Bảng theo dõi cổ phiếu + tiền mã hóa</li>
<li>Flashcards</li>
<li>Ứng dụng đọc - đọc sách, bài viết, v.v. trên kính thông minh của bạn.</li>
</ul>
<h2>Live</h2>
<p>Mentra Live đang tiến triển vượt bậc. Nó có camera HD mới, pin gần như vô hạn nhờ cổng kết nối nguồn, và ước tính 43 gram KỂ CẢ tròng kính. Mentra Live sẽ là một chiếc kính thông minh cực kỳ ấn tượng.<br/><br/><img alt="" src="{{THEME_URI}}/assets/news/mentra_live_old.jpg"/><br/></p>
<p>Để chuẩn bị cho đợt ra mắt tháng 12, đội ngũ đang xây dựng một website hoàn toàn mới, tập trung vào Mentra Live.</p>
<p>Website đó sẽ ra mắt ngày 1 tháng 9. Hãy đón chờ.</p>
<h2>Nex</h2>
<p>Sau chuyến đi gần đây khắp Trung Quốc để hoàn thiện nhiều linh kiện cho Nex, chúng tôi đã đạt được tiến bộ lớn về thiết kế 3D. Carl Allen, Trưởng bộ phận Thiết kế Eyewear của Mentra, đã đến Thâm Quyến để dẫn dắt giai đoạn phát triển tiếp theo của Mentra Nex. Mọi thứ vẫn còn ở giai đoạn sớm và chưa có ngày ra mắt cụ thể, nhưng chúng tôi vẫn kỳ vọng ra mắt Nex vào nửa đầu năm 2026.</p>
<p><img alt="" src="{{THEME_URI}}/assets/news/nex_is_cooking.png"/></p>
<p>Các mẫu thử phần cứng Nex của chúng tôi đang chạy firmware mã nguồn mở và giờ đã kết nối thành công với MentraOS, chạy phụ đề trực tiếp.</p>
<h2>Kết luận</h2>
<p>Cảm ơn mọi người vì đã luôn ủng hộ. Chúng tôi cam kết mỗi ngày để đảm bảo chiếc máy tính cá nhân tiếp theo là mở, do cộng đồng kiểm soát và trao quyền cho người dùng. Tiến bộ của chúng tôi trên hệ điều hành, ứng dụng và phần cứng đang đưa chúng tôi đến gần mục tiêu đó hơn mỗi ngày. Hãy nhớ <a href="https://manage.kmail-lists.com/subscriptions/subscribe?a=YAvT3k&amp;g=RZz9s5&amp;utm_source=blog&amp;utm_campaign=community_update_august_2025_post" rel="noopener" target="_blank">đăng ký nhận bản tin</a> để không bỏ lỡ tin tức và tham gia <a href="{{HOME_URL}}/discord/" rel="noopener" target="_blank">Discord</a> để trò chuyện cùng cộng đồng.<br/><br/>Thân mến,<br/>Đội ngũ Mentra</p>
HTML,
        ],
        [
            'slug' => 'batch-1-almost-sold-out',
            'title' => 'Đợt 1 gần bán hết! Tham gia Câu lạc bộ Nhà sáng lập Mentra',
            'date' => '2026-01-05 19:11:31',
            'excerpt' => 'Đợt 1 của Mentra Live sắp đạt giới hạn số lượng — cơ hội cuối để gia nhập Câu lạc bộ 1.000 Nhà sáng lập trước ngày giao hàng 13 tháng 2.',
            'image' => 'news/1_commerical_bbb38391-aafe-406f-878d-dc3db4dd37b0.png',
            'authors' => [['name' => 'Cayden Pierce', 'role' => 'CEO', 'avatar' => 'news/Cayden_Headshot.png']],
            'body' => <<<'HTML'
<p>Cập nhật nhanh: Đợt 1 của Mentra Live sắp đạt giới hạn số lượng.</p>
<p>Nếu bạn muốn trở thành một phần của <strong>1.000 Nhà sáng lập</strong> sẽ giao hàng vào <strong>ngày 13 tháng 2</strong>, đây chính là lúc để giữ chỗ của bạn. Chỉ còn lại vài suất!<br/><br/><strong>Vì sao Đợt 1 quan trọng</strong><br/><br/>⭐ <strong>Tư cách thành viên Câu lạc bộ 1.000 Nhà sáng lập</strong><br/><br/>Được truy cập sớm vào các Mentra MiniApp sắp ra mắt, tính năng thử nghiệm và những đợt phát hành riêng dành cho cộng đồng.<br/><br/>🚀 <strong>Là người đầu tiên định hình nền tảng</strong><br/><br/>Chủ sở hữu Mentra Live thuộc Câu lạc bộ 1.000 Nhà sáng lập sẽ giúp chúng tôi định hướng những gì tiếp theo cho hệ sinh thái Mentra, tham gia một nhóm Discord riêng cùng các nhà sáng lập.<br/><br/>🎯 <strong>AI và ứng dụng rảnh tay với giá 299 USD</strong><br/><br/>Mentra Live mở khóa livestream góc nhìn người dùng (POV), công cụ AI và nhiều hơn nữa. Chọn cách bạn muốn trải nghiệm thực tế trên chiếc kính thông minh duy nhất có kho ứng dụng riêng.<br/><br/>Giữ chỗ với <a href="{{HOME_URL}}/mentra-live/" rel="noopener" target="_blank" title="Mua Mentra Live">giá Đợt 1 tại đây</a>.</p>
<p><img alt="" src="{{THEME_URI}}/assets/news/1000028631.jpg"/></p>
<p><strong>Demo mới mỗi tuần</strong><br/>Muốn xem demo thực tế và những hình ảnh hé lộ hằng tuần, như hậu trường của chiến dịch quảng cáo "Choose Your Reality" sắp tới? Cảm ơn bạn đã là một phần của cộng đồng Mentra!<br/><br/>Theo dõi mạng xã hội của Mentra 👇.<br/><br/>- <a href="https://x.com/MentraGlass" rel="noopener" target="_blank" title="Mentra X">X</a><br/>- <a href="https://www.reddit.com/r/Mentra/" rel="noopener" target="_blank" title="Mentra Reddit">Reddit</a><br/>- <a href="{{HOME_URL}}/discord/" rel="noopener" target="_blank" title="Mentra Discord">Discord</a><br/>- <a href="https://www.instagram.com/mentraglass/" rel="noopener" target="_blank" title="Mentra Instagram">Instagram</a><br/>- <a href="https://www.facebook.com/MentraGlass/" rel="noopener" target="_blank" title="Mentra Facebook">Facebook</a><br/>- <a href="https://www.linkedin.com/company/mentra-glasses/" rel="noopener" target="_blank" title="Mentra LinkedIn">LinkedIn</a><br/>- <a href="https://www.tiktok.com/@mentraglass" rel="noopener" target="_blank" title="Mentra TikTok">TikTok</a></p>
<p>Cảm ơn bạn đã là một phần của cộng đồng Mentra!</p>
<p>– Đội ngũ Mentra</p>
HTML,
        ],
        [
            'slug' => 'even-realities-g2-supported-on-mentraos',
            'title' => 'MentraOS sắp hỗ trợ Even Realities G2',
            'date' => '2025-11-15 01:40:00',
            'excerpt' => 'Mentra công bố sẽ hỗ trợ đầy đủ Even Realities G2 trên MentraOS, dự kiến hoàn tất vào tháng 1 năm 2026.',
            'image' => 'news/G2_display_86b44e9b-26f7-403f-9c9a-43e2dec74e86.png',
            'authors' => [['name' => 'Cayden Pierce', 'role' => 'CEO', 'avatar' => 'news/Cayden_Headshot.png']],
            'body' => <<<'HTML'
<p>Even Realities đã ra mắt kính thông minh Even Realities G2. So với G1, chúng nhẹ hơn, mỏng hơn, pin tốt hơn, màn hình lớn hơn nhiều, và microphone được cải thiện, trong khi vẫn giữ giá 599 USD.</p>
<h2>Even G1</h2>
<p>Trước đây chúng tôi đã hợp tác với Even Realities và cộng đồng để mang đến hỗ trợ MentraOS đầy đủ trên Even G1, và chúng tôi vẫn đang nỗ lực hỗ trợ hàng nghìn người dùng G1 trên MentraOS.</p>
<h2>Even G2</h2>
<p>Chúng tôi rất vui mừng thông báo sẽ hỗ trợ đầy đủ Even Realities G2. Hỗ trợ MentraOS đầy đủ cho Even Realities G2 dự kiến ra mắt vào tháng 1 năm 2026. Hãy theo dõi blog này và <a href="https://manage.kmail-lists.com/subscriptions/subscribe?a=YAvT3k&amp;g=RZz9s5&amp;utm_source=blog&amp;utm_campaign=even_g2_support_announce_blog_post" rel="noopener" target="_blank">bản tin của chúng tôi</a> để cập nhật thông tin mới nhất sắp tới!</p>
HTML,
        ],
        [
            'slug' => 'making-mentra-live',
            'title' => 'Hành trình tạo nên Mentra Live',
            'date' => '2025-12-21 19:54:03',
            'excerpt' => 'Một góc nhìn sâu về các quyết định kỹ thuật và thách thức trong hành trình nâng cấp camera, khung kính, chip xử lý, âm thanh và cáp sạc của Mentra Live.',
            'image' => 'news/mentra_live_history_93b94f1b-e7e9-479d-b617-4b45963ee804.jpg',
            'authors' => [
                ['name' => 'Alex Israelov', 'role' => 'CTO', 'avatar' => 'news/me_sf_square.png'],
                ['name' => 'Cayden Pierce', 'role' => 'CEO', 'avatar' => 'news/Cayden_Headshot.png'],
            ],
            'body' => <<<'HTML'
<h2>Hành trình tạo nên Mentra Live</h2>
<p>Cảm ơn tất cả những ai đã đặt mua Mentra Live, tham gia hackathon, và gửi phản hồi cùng yêu cầu - các bạn đã góp phần định hình hướng đi của Mentra Live. Bài viết này là một góc nhìn sâu hơn về các quyết định kỹ thuật và thách thức chúng tôi đã đối mặt trong hành trình xây dựng Mentra Live.</p>
<p>Ban đầu chúng tôi dự định giao hàng Mentra Live vào mùa hè 2025. Khi tiến gần đến ngày giao hàng và tự mình sử dụng kính, chúng tôi phát hiện một số vấn đề nghiêm trọng cần giải quyết trước khi có thể giao hàng. Mục tiêu xuyên suốt của chúng tôi luôn là xây dựng một chiếc kính thông minh tuyệt vời mà chính chúng tôi đeo cả ngày, mỗi ngày. Trong 6 tháng qua, chúng tôi đã hiện thực hóa điều đó bằng cách nâng cấp mọi khía cạnh của trải nghiệm. Đây là những thay đổi chúng tôi đã thực hiện:</p>
<h2>Nâng cấp Camera</h2>
<p>Chúng tôi muốn chất lượng hình ảnh hàng đầu, và camera đầu tiên được chọn chưa đạt yêu cầu, nên chúng tôi đã chuyển từ camera 8MP sang camera 12MP chuẩn smartphone.</p>
<p>Camera mới nhỏ hơn đáng kể, giúp giảm trọng lượng và kích thước.</p>
<h2>Thiết kế lại hoàn toàn khung kính để nhẹ và đẹp hơn</h2>
<p>Chúng tôi đã giảm từ ~50 gram xuống còn 43 gram, và thiết kế lại bản lề cùng đệm mũi mới để đeo thoải mái cả ngày.</p>
<h2>Nâng cấp chip xử lý để hỗ trợ livestream</h2>
<p>Ban đầu chúng tôi dùng chip MTK6765. Chip này hoạt động khá tốt khi chụp ảnh, quay video, nhưng gặp vấn đề khi livestream: sau 15+ phút stream chất lượng cao, chip sẽ quá nóng và bị overheat. Chúng tôi đã giải quyết theo vài cách:</p>
<ul>
<li>Nâng cấp lên chip MTK 8766 hiệu quả hơn</li>
<li>Thiết kế lại bo mạch chính của Mentra Live để tách vật lý chip WiFi và chip xử lý, giúp tản nhiệt tốt hơn</li>
</ul>
<h2>Nâng cấp anten WiFi để truyền ảnh nhanh và livestream</h2>
<p>Trước đây chúng tôi có thể chụp ảnh và quay video, nhưng việc truyền dữ liệu khá chậm. Livestream cũng bị giới hạn bởi băng thông. Vì vậy chúng tôi đã nâng cấp anten WiFi để hỗ trợ WiFi 5GHz, livestream video 1080p, và đồng bộ ảnh/video nhanh.</p>
<h2>Nâng cấp loa để phát âm thanh tuyệt vời</h2>
<p>Chúng tôi rất thích nghe cuộc gọi và nhạc/podcast trên Mentra Live. Vì vậy chúng tôi đã đổi sang loa open-ear cao cấp và tinh chỉnh chúng trong một phòng lab âm thanh chuyên nghiệp.</p>
<h2>Thiết kế cáp sạc từ tính riêng cho pin dùng cả ngày</h2>
<p>Qua trò chuyện với cộng đồng Mentra, rõ ràng Mentra Live cần hai điều sau:</p>
<ol>
<li>Thời lượng pin dùng cả ngày khi chụp ảnh, quay video và livestream</li>
<li>Khả năng lập trình dễ dàng</li>
</ol>
<p>Để đạt cả hai điều này, chúng tôi đã phát triển một cáp sạc từ tính riêng. Nó được thiết kế mỏng và nhẹ như tai nghe, gắn từ tính vào mặt sau của Mentra Live. Bạn có thể dùng nó để sạc Mentra Live ngay cả khi đang đeo. Bạn có thể cắm vào iPhone, pin sạc dự phòng, hoặc bất kỳ nguồn USB-C nào khác. Nó cũng hoạt động như cáp USB, nên nhà phát triển hoàn toàn có thể truy cập, chỉnh sửa và thay thế phần mềm của Mentra Live.</p>
<h2>Cách chúng tôi làm cho microphone của kính hoạt động liên tục trên cả iOS và Android</h2>
<p>Các kính AI hiện có kết nối với điện thoại của bạn như tai nghe Bluetooth thông thường. Điều đó rất tốt, vì nó cho phép bạn nghe cuộc gọi, nghe nhạc và xem video bằng loa và microphone của kính.</p>
<p>Tuy nhiên, cách này có một nhược điểm lớn. Nếu bạn đang xem video, nghe cuộc gọi, nghe nhạc, hoặc một ứng dụng khác đang dùng âm thanh trên điện thoại, thì các ứng dụng trên kính của bạn sẽ không thể "nghe" được nữa.<br/><br/>Để giải quyết điều này, chúng tôi đã thiết kế một đường dẫn âm thanh riêng trong MentraOS. Điều đó có nghĩa là dù bạn đang làm gì trên điện thoại, các ứng dụng trên kính vẫn luôn có thể sử dụng microphone của kính.</p>
<p>Cách hoạt động như sau: Mentra Live có một microphone riêng dành cho kênh tùy chỉnh này. Trên kính, microphone đó ghi âm, được nén rất nhỏ (dùng LC3), rồi gửi trực tiếp đến ứng dụng Mentra qua Bluetooth Low Energy (BLE GATT). Từ góc nhìn của điện thoại, không có microphone nào đang hoạt động. Cách này tránh được các quyền truy cập microphone hệ thống, quyền sở hữu của OS, chỉ báo trên giao diện, và các hạn chế nền tảng, trong khi vẫn cho phép truyền âm thanh liên tục và hành vi bắt đầu/dừng tức thì.</p>
<p>Chúng tôi chạy song song cả hai đường dẫn - âm thanh Bluetooth chuẩn cho cuộc gọi và media, cộng với kênh microphone BLE tùy chỉnh cho ứng dụng - để dù điện thoại đang làm gì, kính vẫn luôn có thể "nghe" được.</p>
<h2>Cách chúng tôi suýt trục trặc (rồi khắc phục) hộp sạc</h2>
<p>Sau khi hoàn thiện thiết kế hộp sạc và tiến hành đợt sản xuất đầu tiên với 1.000 chiếc, chúng tôi phát hiện một vấn đề. Nếu hộp bị lật ngược và lắc (như có thể xảy ra trong balo) thì việc sạc sẽ dừng lại. Chúng tôi đã tạm dừng sản xuất hộp cho đến khi tìm ra giải pháp.</p>
<p><iframe src="https://www.youtube.com/embed/QTlWjAeyOFE?autoplay=1&amp;mute=1&amp;loop=1&amp;playlist=QTlWjAeyOFE&amp;playsinline=1"></iframe></p>
<p>Đầu tiên, chúng tôi làm cho lò xo trong hộp chắc hơn. Điều này đảm bảo kính luôn giữ đúng vị trí và không bị rơi ra khi lật ngược. Sau đó, để đảm bảo kết nối liên tục, chúng tôi làm các chân sạc dài hơn.</p>
<p><img alt="" src="{{THEME_URI}}/assets/news/charging_case_upgrade.jpg"/></p>
<h2>Kết luận</h2>
<p>Đầu năm nay, chúng tôi đặt mục tiêu xây dựng một chiếc kính AI tuyệt vời với hệ sinh thái ứng dụng mở.</p>
<p>Suốt năm qua, chúng tôi đã không ngừng nỗ lực cho tầm nhìn đó. Giờ đây, một nửa đội ngũ của chúng tôi đeo Mentra Live mỗi ngày như chiếc kính bình thường. Chúng tôi nghe cuộc gọi, quay lại những chuyến phiêu lưu cuối tuần, livestream lên X, và khám phá những thành phố mới cùng hướng dẫn viên AI, và chúng tôi rất mong được đồng hành cùng bạn.</p>
<p>- Cayden, Israelov và đội ngũ Mentra</p>
HTML,
        ],
        [
            'slug' => 'memcards-the-first-third-party-app-to-launch-on-mentra-market',
            'title' => 'MemCards: ứng dụng bên thứ ba đầu tiên ra mắt trên Mentra Market',
            'date' => '2025-08-29 00:35:00',
            'excerpt' => 'MemCards của studio Tomtau chính thức trở thành ứng dụng bên thứ ba đầu tiên ra mắt trên Mentra Market, đánh dấu bước chuyển sang một hệ sinh thái ứng dụng cộng đồng.',
            'image' => 'news/MemCards.jpg',
            'authors' => [['name' => 'Cayden Pierce', 'role' => 'CEO', 'avatar' => 'news/Cayden_Headshot.png']],
            'body' => <<<'HTML'
<p>Studio phát triển phần mềm Tomtau vừa phát hành <strong>MemCards</strong> trên Mentra Market! Điều đó có nghĩa là bất kỳ ai sở hữu kính thông minh tương thích MentraOS đều có thể tải và dùng MemCards ngay bây giờ. MemCards là ứng dụng flashcard giúp mọi người học ngôn ngữ mới hoặc ghi nhớ bất kỳ thông tin nào.</p>
<p>Mentra rất vui mừng chào đón MemCards của Tomtau là ứng dụng bên thứ ba chính thức đầu tiên trên Mentra Market. Đây đánh dấu bước chuyển sang một thị trường cộng đồng cạnh tranh dành cho ứng dụng kính thông minh.</p>
<h2>Vì sao điều này quan trọng</h2>
<p>Chúng tôi hiểu rằng mọi cuộc chuyển mình lớn của điện toán cá nhân đều được dẫn dắt bởi các nhà phát triển bên thứ ba. VisiCalc, một ứng dụng bên thứ ba, đã biến Apple II từ một cỗ máy dành cho người đam mê thành chiếc máy tính cá nhân không thể thiếu đầu tiên. Các ứng dụng mạng xã hội cũng làm điều tương tự cho di động. Những ứng dụng đột phá hiếm khi đến từ chính nhà tạo nền tảng, mà nảy sinh từ cộng đồng. Chúng tôi sẽ tiếp tục xây dựng các ứng dụng mã nguồn mở cốt lõi, thiết yếu, nhưng những đột phá thực sự sẽ đến từ các nhà phát triển sáng tạo ra những điều mà không ai trong chúng ta có thể đoán trước. MemCards là bước đi đầu tiên hướng tới tương lai đó.</p>
HTML,
        ],
        [
            'slug' => 'mentra-live-featured-in-engadget-gizmodo-new-york-post-and-more',
            'title' => 'Đợt 1 đã bán hết: Mentra Live xuất hiện trên Engadget, Gizmodo và NY Post',
            'date' => '2026-01-29 18:47:41',
            'excerpt' => 'Đợt 1 Mentra Live đã bán hết, và sản phẩm được hàng loạt báo lớn như Engadget, Gizmodo và New York Post đưa tin.',
            'image' => 'news/News_Montage_556772c1-63cb-482b-a58b-f55f41361213.png',
            'authors' => [['name' => 'Cayden Pierce', 'role' => 'CEO', 'avatar' => 'news/Cayden_Headshot.png']],
            'body' => <<<'HTML'
<h3>Đợt 1 đã bán hết!</h3>
<p>Đợt 1 đã chính thức bán hết! Những người may mắn trở thành một phần của <a href="{{HOME_URL}}/tin-tuc/batch-1-almost-sold-out/" rel="noopener" target="_blank" title="Mentra Founder's 1000 Club">Câu lạc bộ 1.000 Nhà sáng lập</a> sẽ bắt đầu nhận Mentra Live từ tuần thứ hai của tháng 2!</p>
<p>Tin vui là nếu bạn bỏ lỡ Đợt 1, Đợt 2 hiện đang mở bán và sẽ giao hàng chỉ vài tuần sau đó, vào tháng 3. Tuy nhiên, Đợt 2 cũng giới hạn ở 1.000 chiếc Mentra Live, nên hãy <a href="{{HOME_URL}}/mentra-live/" rel="noopener" target="_blank" title="Buy Mentra Live Smart Glasses">đặt mua ngay</a>!</p>
<h3>Cập nhật MentraOS</h3>
<p>MentraOS được xây dựng để kính thông minh cảm giác liền mạch và mạnh mẽ ngay từ ngày đầu. Chúng tôi liên tục hoàn thiện tính năng và mở rộng miniapp để kính của bạn ngày càng tốt hơn khi sử dụng.</p>
<p>Đồng sáng lập kiêm CTO của chúng tôi, Israelov, đã liên tục <a href="https://discord.com/channels/973327320247042099/1336767002223837347" rel="noopener" target="_blank" title="Mentra Discord Post">cập nhật trên Discord</a>. 👇</p>
<p><img alt="" src="{{THEME_URI}}/assets/news/Screenshot_2026-01-28_at_12.22.04_AM.png"/></p>
<p><a href="{{HOME_URL}}/discord/" rel="noopener" target="_blank" title="Mentra DIscord">Theo dõi Discord của chúng tôi</a> để cập nhật thêm!</p>
<h3>Mentra Live trên báo chí</h3>
<p>Trong <a href="{{HOME_URL}}/tin-tuc/our-first-press-release-mentra-releases-first-smart-glasses-with-an-app-store/" rel="noopener" target="_blank" title="Mentra Blog Press Release">bài viết trước</a>, chúng tôi đã kể về việc phát hành thông cáo báo chí đầu tiên. Chúng tôi biết mình có một câu chuyện hay và một sản phẩm tuyệt vời, nên rất vui vì các báo đài và phóng viên cũng đồng tình! <br/><br/>Chúng tôi thực sự bất ngờ trước lượng đưa tin, bao gồm <a href="https://www.engadget.com/wearables/mentras-first-smart-glasses-are-open-source-and-come-with-their-own-app-store-150021126.html" rel="noopener" target="_blank" title="Engadget Mentra">Engadget</a>, <a href="https://gizmodo.com/smart-glasses-for-onlyfans-live-streaming-have-arrived-2000710780" rel="noopener" target="_blank" title="Gizmodo Mentra">Gizmodo</a>, <a href="https://www.digitaltrends.com/wearables/your-ray-ban-meta-alternative-is-open-source-and-that-changes-everything/" rel="noopener" target="_blank" title="Digital Trends Mentra">Digital Trends</a>, <a href="https://www.ibtimes.co.uk/smart-glasses-take-unexpected-turn-built-onlyfans-streaming-support-1771354" rel="noopener" target="_blank" title="IBT Mentra">International Business Times</a>, <a href="https://9to5google.com/2026/01/15/mentra-live-smart-glasses-youtube-livestream/" rel="noopener" target="_blank" title="9to5Google Mentra">9to5Google</a>, <a href="https://www.androidpolice.com/these-ray-ban-meta-challengers-will-fulfill-all-your-livestreaming-dreams/" rel="noopener" target="_blank" title="Android Police Mentra">Android Police</a>, <a href="https://nypost.com/2026/01/16/tech/mentra-ai-glasses-lets-onlyfans-models-livestream-hands-free/" rel="noopener" target="_blank" title="New York Post Mentra">New York Post</a>, <a href="https://www.complex.com/pop-culture/a/bernadette-giacomazzo/onlyfans-no-hands-tech-creators" rel="noopener" target="_blank" title="Complex Mentra">Complex</a>, và còn nhiều hơn nữa!<br/><br/><img alt="" src="{{THEME_URI}}/assets/news/News_Montage.png"/><br/>Hãy ghé xem <a href="{{HOME_URL}}/tin-tuc/" rel="noopener" target="_blank" title="Mentra Newsroom">mục tin tức</a> trên <a href="{{HOME_URL}}/" rel="noopener" target="_blank" title="New Mentra Website">website mới của chúng tôi</a>!<br/><br/>Chúng tôi có rất nhiều thông báo và cập nhật mới trong vài tuần tới. Hãy đảm bảo <a href="{{HOME_URL}}/tin-tuc/" rel="noopener" target="_blank" title="Subscribe to Mentra">bạn đang theo dõi chúng tôi</a>!</p>
HTML,
        ],
        [
            'slug' => 'mentra-live-shipping-update',
            'title' => 'Cập nhật giao hàng Mentra Live',
            'date' => '2025-12-21 20:00:51',
            'excerpt' => 'Mentra Live sẽ giao hàng trễ hơn 6 tuần, đến ngày 15 tháng 2, để hoàn thiện hộp sạc, microphone, chống rung camera và các tính năng còn lại.',
            'image' => 'news/mentra_live_sexy.jpg',
            'authors' => [
                ['name' => 'Alex Israelov', 'role' => 'CTO', 'avatar' => 'news/me_sf_square.png'],
                ['name' => 'Cayden Pierce', 'role' => 'CEO', 'avatar' => 'news/Cayden_Headshot.png'],
            ],
            'body' => <<<'HTML'
<p>Chúng tôi phải lùi thời gian giao hàng Mentra Live thêm sáu tuần, và giờ sẽ giao cho toàn bộ khách hàng Đợt 1 tại Mỹ vào ngày 15 tháng 2. Chúng tôi xin lỗi vì sự chậm trễ này.</p>
<p>Phần cứng Mentra Live đã hoàn thiện và hoạt động trơn tru từ đầu đến cuối. Khoảng thời gian thêm này là để hoàn thiện một số chi tiết quan trọng cuối cùng để trải nghiệm cảm giác kỳ diệu và liền mạch ngay từ ngày đầu.<br/><br/>Xem video mở hộp và demo trực tiếp Mentra Live tại đây:<br/><br/><iframe allowfullscreen="" height="315" src="https://www.youtube.com/embed/-96QvIVzcMc" title="YouTube video player" width="560"></iframe></p>
<h2>Những gì đã hoạt động tốt hôm nay</h2>
<p>Mentra Live hiện đã hoạt động đầy đủ. Ngay hôm nay, kính đã hỗ trợ:</p>
<ul>
<li>Chụp ảnh và quay video, đồng bộ ngay lập tức về điện thoại</li>
<li>Livestream trực tiếp lên YouTube, Twitch và X</li>
<li>Phát nhạc và podcast qua kính</li>
<li>Gọi điện thoại hoàn toàn rảnh tay</li>
<li>Mentra AI, nhìn thấy những gì bạn nhìn thấy</li>
</ul>
<p>Phần lớn những điều này được thể hiện trong các video demo và mở hộp ở trên.</p>
<h2>Những gì chúng tôi đang hoàn thiện (và vì sao chúng tôi lùi lịch)</h2>
<p>Phần cứng đã ổn định. Phần việc còn lại là các thay đổi phần mềm cuối cùng để trải nghiệm ngày đầu tiên thật sự kỳ diệu.</p>
<ul>
<li>Cập nhật hộp sạc: chúng tôi phát hiện nếu lật ngược hộp và lắc, kính sẽ ngừng sạc. Chúng tôi phải khắc phục điều này cho những người dùng để Mentra Live trong balo. Vấn đề đã được sửa và các hộp sạc phiên bản mới đang được sản xuất</li>
<li>Hoàn thiện giải pháp microphone tùy chỉnh để microphone của Mentra Live hoạt động liên tục và đáng tin cậy</li>
<li>Ổn định hình ảnh camera: hoàn thiện tính năng chống rung bằng phần mềm để hình ảnh mượt mà khi đi lại và di chuyển</li>
<li>Mentra Notes: tự động ghi chú bằng AI với Mentra Live</li>
<li>Gọi video góc nhìn người dùng (POV): chia sẻ hình ảnh từ camera trên kính trong cuộc gọi</li>
</ul>
<h2>Quá trình chế tạo Mentra Live</h2>
<p><iframe allowfullscreen="" height="560" src="https://www.youtube.com/embed/bhtVIJdsMS4?mute=1" width="315"></iframe></p>
<h2>Lịch giao hàng cập nhật</h2>
<p>Mentra Live sẽ giao hàng vào ngày 15 tháng 2 năm 2026.</p>
<p>Chúng tôi sẽ tiếp tục chia sẻ cập nhật (và demo) khi hoàn thiện những phần cuối cùng.</p>
<p>Cảm ơn sự kiên nhẫn của các bạn - chúng tôi làm điều này để đảm bảo Mentra Live thực sự kỳ diệu ngay từ ngày đầu, và xứng đáng với thời gian chờ đợi.</p>
<p>- Cayden, Israelov và đội ngũ Mentra</p>
HTML,
        ],
        [
            'slug' => 'mentra-roadmap-update-moving-to-miniapps-on-the-phone',
            'title' => 'Cập nhật lộ trình Mentra: Chuyển miniapp sang chạy trên điện thoại',
            'date' => '2026-06-09 04:32:05',
            'excerpt' => 'Mentra công bố kiến trúc mới của MentraOS: chuyển miniapp từ chạy trên cloud sang chạy cục bộ ngay trên điện thoại, cùng với Mentra Bluetooth SDK dành cho triển khai doanh nghiệp.',
            'image' => 'Mockup_OS_Phone_Hand.png',
            'authors' => [['name' => 'Cayden Pierce', 'role' => 'CEO', 'avatar' => 'news/Cayden_Headshot.png']],
            'body' => <<<'HTML'
<p>Trong năm qua, MentraOS đã phát triển nhờ chính cộng đồng này.</p>
<p>Các nhà phát triển đã xây dựng ứng dụng, thử nghiệm ý tưởng mới, gửi phản hồi trực tiếp, tranh luận về kiến trúc của chúng tôi, và giúp chúng tôi hiểu phần mềm kính thông minh thực sự cần trở thành điều gì. Cùng lúc đó, chúng tôi đã hợp tác chặt chẽ với nhiều đối tác OEM đang chuẩn bị ra mắt sản phẩm chạy MentraOS trong năm nay và năm sau. Phản hồi của họ khá tương đồng với những gì chúng tôi nghe được từ cộng đồng: nền tảng cần trở nên cục bộ hơn, đáng tin cậy hơn, độ trễ thấp hơn, riêng tư hơn và dễ tích hợp sâu hơn.</p>
<p>Cảm ơn tất cả những ai đã giúp chúng tôi đi đến đây và chia sẻ phản hồi về MentraOS. Chúng tôi rất vui vì đây đã trở thành một dự án do cộng đồng dẫn dắt, với hàng nghìn nhà phát triển, người dùng và công ty cùng góp phần định hình tương lai của kính thông minh.</p>
<p>Trong bài viết này, chúng tôi sẽ trình bày giai đoạn tiếp theo của hành trình đó.</p>
<h2>Cách MentraOS hoạt động hiện nay</h2>
<p>Hiện tại, các ứng dụng MentraOS chạy trên cloud.</p>
<p>Kiến trúc hiện tại trông đại khái như sau:</p>
<p><strong>Kính → điện thoại → relay cloud của Mentra → ứng dụng cloud của nhà phát triển, và ngược lại</strong></p>
<p>Mô hình này giúp chúng tôi triển khai nhanh. Nó cho phép MentraOS hỗ trợ nhiều loại kính thông minh, đạt hàng chục nghìn lượt tải, và cho phép nhà phát triển ra mắt ứng dụng mà không cần xây dựng tích hợp di động gốc (native). Nó đã chứng minh rằng kính thông minh cần một nền tảng ứng dụng.</p>
<p>Nhưng kiến trúc hiện tại có giới hạn. Vì ứng dụng chạy trên cloud, điện thoại cần một relay để kết nối kính với các ứng dụng đó. Nếu không, mỗi ứng dụng sẽ cần luồng dữ liệu riêng cho âm thanh, hình ảnh, camera và điều khiển, điều này sẽ nhanh chóng gây hại cho dung lượng dữ liệu, thời lượng pin, độ trễ và độ ổn định.</p>
<p>Relay đã giải quyết một vấn đề quan trọng, nhưng cũng tạo ra những đánh đổi mới. Một nền tảng xoay quanh hạ tầng cloud của Mentra làm dấy lên lo ngại về quyền kiểm soát, quyền riêng tư, triển khai doanh nghiệp, và quyền sở hữu của OEM. Chúng tôi đã nỗ lực nhiều để cải thiện những vấn đề đó, nhưng chúng không thể được giải quyết triệt để với kiến trúc hiện tại.</p>
<p>Một ví dụ đơn giản là việc nhấn nút. Nếu một ứng dụng cần phản hồi khi có người nhấn nút vật lý trên kính, sự kiện đó sẽ đi từ kính, đến điện thoại, đến relay cloud của Mentra, đến ứng dụng cloud của nhà phát triển, và ngược lại. Ngay cả trong trường hợp tốt, điều đó có thể mất đến hàng trăm mili giây trước khi ứng dụng phản hồi được. Đối với tương tác trên kính thông minh, như vậy là chưa đủ tốt.</p>
<h2>Kiến trúc mới: ứng dụng chạy cục bộ trên điện thoại</h2>
<p>Một yêu cầu cốt lõi của MentraOS là kính thông minh phải có thể chạy nhiều ứng dụng cùng lúc, dù kính chỉ có một kết nối đang hoạt động với điện thoại.</p>
<p>Điều đó có nghĩa là các ứng dụng không thể tự kết nối độc lập với kính. Nếu mỗi ứng dụng đều sở hữu kết nối Bluetooth riêng, bạn sẽ bị giới hạn chỉ dùng một ứng dụng tại một thời điểm, và mỗi nhà phát triển sẽ phải xây dựng lại cùng một tích hợp với kính.</p>
<p>MentraOS 3.0 giải quyết điều này bằng cách đưa tất cả ứng dụng vào trong một ứng dụng host duy nhất trên điện thoại.</p>
<p>Các ứng dụng OEM đi kèm MentraOS sẽ bao gồm Mentra runtime. Nếu một hãng OEM ra mắt kính chạy MentraOS, ứng dụng di động của họ có thể host cùng một runtime cục bộ như ứng dụng Mentra. Với tư cách nhà phát triển, ứng dụng của bạn sẽ chạy cục bộ bên trong runtime đó và hoạt động trên mọi loại kính hỗ trợ MentraOS.</p>
<p>Ứng dụng Mentra là ứng dụng host mã nguồn mở, có thể tùy biến, dùng để xây dựng, kiểm thử và phân phối ứng dụng trên các loại kính được hỗ trợ.</p>
<p>Kiến trúc mới trông đại khái như sau:</p>
<p><strong>Kính → ứng dụng điện thoại → miniapp cục bộ, và ngược lại</strong></p>
<p>Miniapp sẽ được xây dựng bằng <strong>Mentra Miniapp SDK</strong>. Chúng chạy cục bộ trên điện thoại, bên trong ứng dụng host, và dùng Mentra runtime để điều khiển đầu vào/đầu ra của kính, vòng đời ứng dụng, quyền truy cập, lưu trữ, kết nối mạng, và các tính năng gốc của điện thoại.</p>
<p>Đối với nhà phát triển, điều này có nghĩa là:</p>
<ul>
<li>độ trễ thấp hơn nhiều</li>
<li>độ tin cậy tốt hơn</li>
<li>hành vi ứng dụng ưu tiên cục bộ (local-first)</li>
<li>kết nối trực tiếp đến backend của riêng bạn</li>
<li>lưu trữ dữ liệu cục bộ</li>
<li>các lựa chọn tốt hơn về quyền riêng tư và tuân thủ</li>
<li>ít phụ thuộc hơn vào hạ tầng cloud của Mentra</li>
<li>một ứng dụng duy nhất có thể hoạt động trên nhiều loại kính</li>
</ul>
<p>Đây chính là kiến trúc khiến MentraOS thực sự trở thành một hệ điều hành cho kính thông minh: nhanh, cục bộ, mở rộng được, và được xây dựng cho những triển khai thực sự quan trọng.</p>
<h2>Mentra Bluetooth SDK</h2>
<p>Khi ngày càng nhiều công ty bắt đầu triển khai và thử nghiệm với MentraOS, chúng tôi nhận được một nhu cầu nhất quán từ các doanh nghiệp, hãng OEM và đội ngũ doanh nghiệp: một số triển khai cần quyền kiểm soát ứng dụng trực tiếp, hỗ trợ on-premise, hành vi offline, các yêu cầu tuân thủ nghiêm ngặt hơn, hoặc tích hợp sâu hơn vào một ứng dụng di động sẵn có.</p>
<p>Vì vậy, chúng tôi đã module hóa MentraOS để lớp kết nối Bluetooth giữa điện thoại và kính có thể được sử dụng độc lập.</p>
<p>Điều đó giờ đã khả dụng dưới dạng <strong>Mentra Bluetooth SDK</strong>.</p>
<p>Chúng tôi tin rằng khả năng truy cập trực tiếp nên chính thức, có tài liệu đầy đủ, và tương thích với hệ sinh thái MentraOS rộng lớn hơn, thay vì điều gì đó các đội ngũ phải reverse engineer.</p>
<p>Bluetooth SDK phù hợp nhất cho:</p>
<ul>
<li>triển khai B2B</li>
<li>ứng dụng đơn mục đích</li>
<li>tích hợp trực tiếp vào một ứng dụng di động sẵn có</li>
<li>các trường hợp một ứng dụng điều khiển kính trực tiếp</li>
</ul>
<p>Điểm đánh đổi là với Bluetooth SDK, bạn phải tự sở hữu mọi thứ bên trên lớp kết nối Bluetooth. Bạn cần tự xây dựng hoặc tích hợp ASR, TTS, lớp AI, luồng quét và kết nối kính, phân phối ứng dụng, quyền truy cập, xử lý vòng đời, và hành vi tiết kiệm năng lượng của riêng mình.</p>
<p>Với Mentra Miniapp SDK, những phần nền tảng đó đã có sẵn. Thông thường sẽ nhanh hơn 3–10 lần khi xây dựng trên Miniapp SDK, và ứng dụng của bạn vẫn có thể chạy trên toàn hệ sinh thái MentraOS.</p>
<p>Bạn có thể bắt đầu tại đây:</p>
<p><a href="https://github.com/Mentra-Community/Mentra-Bluetooth-SDK-Starter-Kit">https://github.com/Mentra-Community/Mentra-Bluetooth-SDK-Starter-Kit</a></p>
<p>Với hầu hết nhà phát triển, <strong>Mentra Miniapp SDK</strong> là lựa chọn tốt hơn. Nó mang lại cho bạn cả nền tảng ứng dụng, chứ không chỉ lớp kết nối.</p>
<p>Bluetooth SDK mang lại quyền kiểm soát trực tiếp cho các triển khai đơn mục đích.</p>
<p>Mentra Miniapp SDK mang lại nền tảng để xây dựng ứng dụng.</p>
<h2>Lộ trình và quá trình chuyển đổi</h2>
<p><em>Lịch trình cập nhật: ngày 20 tháng 7 năm 2026</em></p>
<p><strong>Ngày 10 tháng 6:</strong></p>
<ul>
<li>Ngừng hỗ trợ tích cực cho miniapp Cloud SDK trên Mentra Live. Chúng vẫn hoạt động, nhưng chúng tôi không còn phát triển thêm Cloud SDK.</li>
<li>Từ nay trở đi, nhà phát triển xây dựng tích hợp camera trực tiếp hoặc B2B cho Mentra Live nên dùng <a href="https://github.com/Mentra-Community/Mentra-Bluetooth-SDK-Starter-Kit"><strong>Mentra Bluetooth SDK</strong></a>, hiện đã khả dụng cho Android, iOS và React Native.</li>
</ul>
<p><strong>Ngày 3 tháng 8:</strong></p>
<ul>
<li>MentraOS 3.0 chính thức ra mắt!</li>
<li>Ngừng hỗ trợ toàn bộ miniapp Cloud SDK. Miniapp Cloud SDK sẽ không còn hoạt động trong ứng dụng Mentra chính từ thời điểm này.</li>
<li>Tất cả miniapp chính thức của Mentra sẽ được chuyển sang Miniapp SDK mới trong đợt cập nhật này và tiếp tục hoạt động bình thường.</li>
<li>Miniapp SDK mới phát hành bản beta riêng tư. Liên hệ để được truy cập sớm.</li>
<li>Để tiếp tục dùng các miniapp Cloud SDK cũ, chúng tôi sẽ cung cấp <a href="{{HOME_URL}}/legacy/" title="MentraOS Legacy">MentraOS Legacy</a>.</li>
</ul>
<p><strong>Tháng 9/2026:</strong></p>
<ul>
<li>Ra mắt công khai Miniapp SDK mới, dùng để phát triển miniapp cho kính có màn hình, như Even Realities G2, Even Realities G1 và Vuzix Z100.</li>
</ul>
<p><strong>Tháng 10/2026:</strong></p>
<ul>
<li>Ngừng hỗ trợ <a href="{{HOME_URL}}/legacy/" title="MentraOS Legacy">MentraOS Legacy</a>. Miniapp Cloud SDK không còn hoạt động.</li>
</ul>
<p>Với bất kỳ ai đang có miniapp hiện có, chúng tôi sẽ công bố hướng dẫn chuyển đổi chi tiết và hỗ trợ nếu bạn gặp vấn đề trong quá trình chuyển đổi.</p>
<h2>Tiếp tục hỗ trợ Cloud</h2>
<p>Cộng đồng này đã xây dựng hơn 1.000 ứng dụng bằng Cloud SDK. Một số ứng dụng trong đó đã được triển khai trong thực tế và vẫn đang được sử dụng đến ngày nay. Chúng tôi muốn đảm bảo duy trì hỗ trợ phù hợp trong khi đồng hành cùng bạn chuyển sang Mentra Miniapp SDK mới. Chúng tôi sẽ tiếp tục hỗ trợ legacy cho Cloud SDK trong ít nhất 2 tháng sau khi Miniapp SDK ra mắt. Để biết chi tiết cách tiếp tục dùng Cloud SDK sau khi Miniapp SDK ra mắt, xem <a href="{{HOME_URL}}/legacy/">MentraOS Legacy</a>.</p>
<h2>Vì sao điều này tốt hơn cho nhà phát triển, người dùng và doanh nghiệp</h2>
<p>Thay đổi này nhằm biến MentraOS thành một nền tảng tốt hơn để bạn xây dựng và triển khai ứng dụng.</p>
<p>Ứng dụng xây dựng bằng Mentra Miniapp SDK sẽ nhanh hơn, đáng tin cậy hơn, riêng tư hơn và mạnh mẽ hơn. Nhà phát triển sẽ có thể xây dựng ứng dụng phản hồi tức thì, hoạt động gần với edge hơn, lưu trữ dữ liệu cục bộ, kết nối trực tiếp đến backend riêng, và vẫn phân phối qua hệ sinh thái Mentra.</p>
<p>Người dùng sẽ có ứng dụng phản hồi nhanh và đáng tin cậy hơn. Doanh nghiệp có một lộ trình rõ ràng hơn cho quyền riêng tư, tuân thủ, hành vi offline, và tích hợp trực tiếp vào hệ thống của riêng họ. Các hãng OEM có nhiều quyền kiểm soát hơn với trải nghiệm người dùng của riêng họ, trong khi vẫn cho nhà phát triển một runtime duy nhất hoạt động trên nhiều loại kính khác nhau.</p>
<p>Đây chính là phiên bản MentraOS mà cộng đồng đã mong đợi: ứng dụng cục bộ, độ trễ thấp hơn, độ tin cậy tốt hơn, và một SDK duy nhất cho thế hệ kính thông minh tiếp theo.</p>
HTML,
        ],
        [
            'slug' => 'mentraos-1-0-launch-hackathon-smart-glasses-hackathon-march-2-2025',
            'title' => 'Hackathon ra mắt MentraOS 1.0 - Hackathon kính thông minh tháng 3/2025',
            'date' => '2025-03-04 01:17:00',
            'excerpt' => 'Tổng kết hackathon kính thông minh đầu tiên của Mentra tại San Francisco, với các dự án nổi bật và ba đội chiến thắng.',
            'image' => 'news/Screenshot_from_2025-11-29_17-22-44.png',
            'authors' => [['name' => 'Cayden Pierce', 'role' => 'CEO', 'avatar' => 'news/Cayden_Headshot.png']],
            'body' => <<<'HTML'
<p>Từ ngày 1 đến 2 tháng 3 năm 2025, chúng tôi tổ chức hackathon kính thông minh đầu tiên tại trụ sở Mentra. 20 hacker xuất sắc được chọn lọc đã đến trụ sở Mentra tại Dogpatch, San Francisco để cùng xây dựng tương lai của giao diện cá nhân. Chúng tôi tận dụng cơ hội này để nhận phản hồi trực tiếp từ các nhà phát triển khi họ xây dựng ứng dụng trên nền tảng mã nguồn mở của chúng tôi.</p>
<p><a href="https://x.com/caydengineer/status/1896351189333385330t=5M1gCSY2KIjFmF6THjxPzw&amp;amp;s=19">Xem video về sự kiện này.</a></p>
<h2>Dự án nổi bật</h2>
<ul>
<li>Bộ lên dây đàn guitar trên kính</li>
<li>Bản nhạc trên kính</li>
<li>Công cụ hỗ trợ ADHD trên kính</li>
<li>Khung ngắm camera trên kính</li>
<li>Máy đọc sách điện tử trên kính</li>
</ul>
<h2>Người thắng cuộc</h2>
<p><strong>Giải Nhất: Augmented Chords</strong> - bản nhạc và hợp âm trên kính thông minh giúp nhạc sĩ rảnh tay. Chúng tôi đã có nhiều buổi chơi nhạc cùng nhau suốt hackathon.</p>
<p><strong>Giải Nhì: FilippeOS</strong> - xem khung ngắm camera của kính thông minh ngay trên kính. Dùng cử chỉ tay để zoom và xoay hình ảnh vào khung. Trải nghiệm cực kỳ vui và chúng tôi đã chụp một bức ảnh nhóm.</p>
<p><strong>Giải Ba: Ulrich's Proactive Debater</strong> - AI trên kính thông minh nghe khi có ai đó nói điều bạn không đồng tình và tự động hiển thị các luận điểm phản biện lên màn hình. Tốc độ dưới 500ms thực sự đáng kinh ngạc.</p>
<p><strong>Giải phụ</strong>: ứng dụng <a href="https://x.com/caydengineer/status/1899510115549724741?t=4IshQMbqITVNFrc15Wtqkg">"LOCK IN" của Sean C</a>.</p>
<h2>Kết luận</h2>
<p><a href="https://manage.kmail-lists.com/subscriptions/subscribe?a=YAvT3k&amp;g=RZz9s5&amp;utm_source=blog&amp;utm_campaign=mentraos_1p0_hackathon_blog_post" rel="noopener" target="_blank">Đăng ký nhận bản tin</a> và tham gia Discord của chúng tôi để không bỏ lỡ các hackathon sắp tới! Hẹn gặp lại ở lần tới!</p>
HTML,
        ],
        [
            'slug' => 'mentraos-the-smart-glasses-operating-system-app-store',
            'title' => 'Vì sao chúng tôi xây dựng MentraOS: Hệ điều hành cho kính thông minh',
            'date' => '2025-02-20 23:53:00',
            'excerpt' => 'Vì sao Mentra xây dựng MentraOS: hệ điều hành mã nguồn mở, do cộng đồng kiểm soát cho kính thông minh, hướng tới tương lai điện toán không gian.',
            'image' => 'news/MentraOS_store_1_captions_crop_64ce2518-9ccf-41f3-8337-be7fed2e99a1.png',
            'authors' => [['name' => 'Cayden Pierce', 'role' => 'CEO', 'avatar' => 'news/Cayden_Headshot.png']],
            'body' => <<<'HTML'
<p>Chúng tôi đang xây dựng hệ điều hành mở cho kính thông minh.</p>
<p>Chúng tôi tin rằng xây dựng một hệ điều hành và hệ sinh thái mã nguồn mở, do cộng đồng dẫn dắt cho kính thông minh là con đường tốt nhất để đảm bảo thiết bị điện toán cá nhân tiếp theo nâng tầm nhân loại theo hướng tích cực. Chúng tôi đang xây dựng tương lai mở, tự chủ và được tăng cường (augmented) đó.</p>
<h2>Vì sao xây dựng MentraOS</h2>
<p>Hệ sinh thái hệ điều hành di động hiện đại chưa sẵn sàng cho kính thông minh. Nhưng MentraOS thì có.</p>
<p>MentraOS cho phép mọi ứng dụng:</p>
<ol>
<li>Chạy ngay lập tức trên bất kỳ loại kính thông minh nào.</li>
<li>Truy cập liên tục vào đầu vào/đầu ra của kính thông minh.</li>
<li>Chạy liên tục - không bị iOS hay Android tắt giữa chừng.</li>
<li>Được khám phá - tiếp cận mọi người dùng kính thông minh.</li>
</ol>
<p>Đối với người dùng, điều này có nghĩa là bạn có thể chạy nhiều ứng dụng chủ động cùng lúc mà không cần khởi động lại hay bị ngắt kết nối. MentraOS là một <strong>hệ điều hành ngữ nghĩa (semantic OS)</strong> - bạn kiểm soát những gì bạn thấy và khi nào bạn thấy nó.<br/><br/>Các nền tảng hiện nay quyết định thay chúng ta dựa trên các chỉ số tương tác và doanh thu quảng cáo. MentraOS đảo ngược mô hình đó - bạn quyết định bộ lọc cho nội dung và thực tại của chính mình.</p>
<h2>Niềm tin cốt lõi</h2>
<p><strong>AI chủ động</strong> - Chúng tôi đang chuyển từ các công cụ tường minh sang các AI agent chủ động, tăng cường tư duy con người. Tư duy lai (hybrid thinking) thực sự đòi hỏi việc nắm bắt ngữ cảnh liên tục và một giao diện băng thông cao đến tâm trí. Kính thông minh là lựa chọn hoàn hảo cho điều này: chúng nhìn thấy những gì ta nhìn thấy, nghe những gì ta nghe, và có thể điều chỉnh những gì ta cảm nhận.</p>
<p><strong>Underspec</strong> - Để duy trì trọng lượng dưới 40 gram và pin dùng được 8+ giờ, kính thông minh dùng cả ngày phải giữ mức tiêu thụ năng lượng thấp. Chúng không thể chạy các ứng dụng nặng một cách gốc (natively) - MentraOS chính là cầu nối cho khoảng cách đó.</p>
<p><strong>Memes Matter (Ý tưởng lan tỏa quan trọng)</strong> - Kính thông minh sẽ định nghĩa lại cách ý tưởng lan truyền. Các thuật toán hiện tại khuếch đại sự phẫn nộ và quảng cáo. MentraOS trao cho mỗi cá nhân quyền kiểm soát những ý tưởng mà họ truyền tải và tiếp nhận.</p>
<p><strong>Đứng ngoài đường đi</strong> - Hệ điều hành chỉ nên làm những gì một hệ điều hành thực sự cần làm. MentraOS cung cấp giao diện tối giản, một SDK cho phép toàn quyền, và một kho ứng dụng phi tập trung. Nó trao quyền cho nhà phát triển và người dùng - không phải cho các tập đoàn.</p>
<h2>Tầm nhìn</h2>
<p>Mục tiêu của chúng tôi là một tương lai mã nguồn mở, do từng cá nhân kiểm soát, phi tập trung cho kính thông minh - một tương lai tăng cường trí tuệ con người thay vì kiểm soát nó.</p>
<p>Chúng tôi xem kính thông minh là bước tiến vĩ đại tiếp theo của nhân loại - một <em>ngoại vỏ não (exocortex)</em> nhân lên trí tuệ của chúng ta gấp triệu lần.<br/><br/>Hãy cùng chiến thắng trong cuộc đua giành nền tảng điện toán đa dụng tiếp theo cho xã hội.</p>
<h2>Thực tế tăng cường không gian (Spatial AR)</h2>
<p>Kính thông minh dạng HUD (màn hình hiển thị trước mắt) sẽ lớn mạnh như smartphone, và thực tế tăng cường không gian (spatial AR) sẽ lớn mạnh như Internet. Spatial AR là giai đoạn tiếp theo của Internet - hợp nhất thế giới số và thế giới vật lý. Kính HUD cho phép <strong>AR ngữ nghĩa (semantic AR)</strong>, thay đổi tư duy và hội thoại thông qua một giao diện phủ lớp.<br/><br/><strong>Spatial AR</strong> sẽ đến sau - khi kính có thể hiểu được môi trường vật lý xung quanh. Còn hiện tại, các thiết bị HUD nhẹ đã mở khóa tiềm năng to lớn. MentraOS phát triển song song với lộ trình này. Nó giải quyết các bài toán HUD của hiện tại trong khi tiến dần tới spatial AR đầy đủ và cuối cùng là giao diện não-máy tính (BCI).<br/><br/>Hôm nay: tối ưu cho các thiết bị HUD nhẹ.<br/>Ngày mai: nền tảng cho điện toán không gian và nhận thức đầy đủ.</p>
<h2>Muốn giúp đỡ hoặc tìm hiểu thêm?</h2>
<p>Liên hệ: <a href="mailto:team@mentraglass.com">team@mentraglass.com</a></p>
HTML,
        ],
        [
            'slug' => 'our-first-press-release-mentra-releases-first-smart-glasses-with-an-app-store',
            'title' => 'Mentra ra mắt kính thông minh đầu tiên có kho ứng dụng riêng',
            'date' => '2026-01-15 15:00:02',
            'excerpt' => 'Thông cáo báo chí chính thức đầu tiên của Mentra, công bố Mentra Live — chiếc kính thông minh đầu tiên có kho ứng dụng riêng.',
            'image' => 'news/WomanWearingMentraLive_78ff9fd3-f995-45b4-9377-c79b8c26577c.png',
            'authors' => [['name' => 'Cayden Pierce', 'role' => 'CEO', 'avatar' => 'news/Cayden_Headshot.png']],
            'body' => <<<'HTML'
<p>Chúng tôi sẽ giao 1.000 chiếc Mentra Live đầu tiên chỉ trong một tháng nữa! Đội ngũ tại San Francisco và Thâm Quyến đã <a href="{{HOME_URL}}/tin-tuc/making-mentra-live/" rel="noopener" target="_blank" title="The building of Mentra Live">nỗ lực rất nhiều</a> để mang Mentra Live đến với bạn!<br/><br/>Xem thông cáo báo chí bên dưới:<br/></p>
<p><img alt="" src="{{THEME_URI}}/assets/news/WomanWearingMentraLive.png"/></p>
<p style="text-align: center; padding-left: 40px;"><strong>Mentra ra mắt kính thông minh đầu tiên có kho ứng dụng riêng</strong></p>
<p style="padding-left: 40px;"><em>Được hậu thuẫn bởi Amazon, Toyota Ventures, YC và nhiều hơn nữa, công ty mã nguồn mở này đã gọi vốn 8 triệu USD trong năm 2025 và là chiếc kính thông minh duy nhất có kho ứng dụng riêng</em></p>
<p style="padding-left: 40px;"><span>NGÀY 15 THÁNG 1, 2026</span> — Mentra, nhà phát triển hệ điều hành kính thông minh hàng đầu, hôm nay công bố bắt đầu giao hàng kính thông minh Mentra Live. Mentra Live là chiếc kính đầu tiên do công ty phát hành và là chiếc kính thông minh duy nhất có kho ứng dụng riêng. Với trọng lượng 43 gram, Mentra Live nằm trong số những chiếc kính nhẹ nhất ngành, trang bị camera HD 12MP để chụp ảnh và quay video, livestream có chống rung lên bất kỳ nền tảng mạng xã hội nào, tích hợp AI, và khả năng nghe cuộc gọi cùng nghe nhạc.</p>
<p style="padding-left: 40px;">MentraOS, hệ điều hành mã nguồn mở của Mentra, có bộ công cụ phát triển phần mềm (SDK), nơi các nhà phát triển đã xây dựng ứng dụng kính thông minh cho Mentra MiniApp Store từ đầu năm 2025. Mentra Live cũng là chiếc kính thông minh đầu tiên cho phép người dùng livestream lên mọi nền tảng mạng xã hội, bao gồm X, YouTube, Twitch, OnlyFans, Instagram và nhiều hơn nữa.</p>
<p style="padding-left: 40px;">"Kính thông minh chỉ có thể phát huy hết tiềm năng nếu hệ sinh thái luôn mở, dễ tiếp cận và do cộng đồng dẫn dắt, thay vì đóng kín và bị các hãng công nghệ lớn thống trị," Cayden Pierce, CEO và đồng sáng lập Mentra, chia sẻ. "Mentra Live và MentraOS trao quyền cho người dùng tự chọn ứng dụng và kiểm soát dữ liệu của mình, đồng thời cho phép nhà phát triển xây dựng trên một nền tảng mở."</p>
<p style="padding-left: 40px;">Được hậu thuẫn bởi Y Combinator, Rich Miner (đồng sáng lập Android), Jawed Karim (đồng sáng lập YouTube), Paul Graham, Eric Migicovsky (nhà sáng lập Pebble), Amazon, Toyota Ventures và Hartmann Capital, cùng nhiều nhà đầu tư khác, Mentra đã gọi vốn vòng hạt giống 8 triệu USD vào tháng 7 năm 2025. Với giá 299 USD, chỉ có 1.000 chiếc Mentra Live được mở bán cho Đợt 1, giao hàng ngày 15 tháng 2. Công ty sẽ mở bán Đợt 2 với số lượng giới hạn, giao hàng muộn hơn trong Quý 1.</p>
<p style="padding-left: 40px;">Mentra MiniApp Store là kho ứng dụng duy nhất dành cho kính thông minh, cho phép người dùng truy cập miniapp qua ứng dụng iOS và Android của Mentra. Các miniapp nổi bật từ nhiều nhà phát triển khác nhau gồm Merge Proactive AI, Poker Probability, Chess Cheater, AI Notes và nhiều hơn nữa.</p>
<p style="padding-left: 40px;">"Điều khiến Mentra khác biệt so với nhiều công ty kính thông minh mà chúng tôi đã thẩm định là sự kết hợp hiếm có giữa thiết kế phần cứng lấy người dùng làm trung tâm và chiến lược phần mềm dựa trên hiệu ứng mạng lưới," Felix Hartmann, Managing Partner của Hartmann Capital, chia sẻ. "Trong khi nhiều hãng khác dẫn đầu bằng những màn demo hoành tráng hoặc cố gắng chế tạo những thiết bị quá tải tính năng mà ít người thực sự dùng, Mentra đã âm thầm xây dựng một sản phẩm sẵn sàng ra thị trường mà hàng nghìn người đang sử dụng trong năm nay."</p>
<p style="padding-left: 40px;">Thông số kỹ thuật Mentra Live:</p>
<ul>
<li style="list-style-type: none;">
<ul>
<li>Kích thước: 162D x 148R x 47C (mm)</li>
<li>Trọng lượng: 43g</li>
<li>Chipset: CPU Mediatek MTK8766 kết hợp MCU tiết kiệm năng lượng (xử lý hai chip để tăng thời lượng pin)</li>
<li>Hệ điều hành: MentraOS</li>
<li>Pin:
<ul>
<li>Kính riêng lẻ: 12+ giờ/260mAh</li>
<li>Hộp sạc: cộng thêm 50+ giờ/2.200mAh</li>
<li>Cáp Infinity: pin gần như vô hạn khi sạc kính từ điện thoại hoặc pin dự phòng</li>
</ul>
</li>
<li>Camera: 12MP/video HD</li>
<li>Góc nhìn camera (FOV): 119 độ</li>
<li>Microphone: 3 (cho phép đa nhiệm âm thanh)</li>
<li>Loa: âm thanh Stereo</li>
<li>Điện thoại hỗ trợ: iOS 15.1+ và Android 12+</li>
</ul>
</li>
</ul>
<p style="padding-left: 40px;">Tìm hiểu thêm về Mentra tại: <a href="{{HOME_URL}}/">https://mentraglass.com/</a></p>
<p style="padding-left: 40px;"><strong>Về Mentra</strong></p>
<p style="padding-left: 40px;">Thành lập năm 2024, Mentra là nền tảng kính thông minh hàng đầu, kết hợp một hệ điều hành mã nguồn mở (OS), hệ sinh thái nhà phát triển và phần cứng dành cho người tiêu dùng. Mentra trao cho nhà phát triển khả năng xây dựng, phát hành và chạy ứng dụng trên kính thông minh thông qua SDK và MiniApp Store. Mentra được thiết kế từ nền tảng để tạo ra một nền móng linh hoạt, tôn trọng quyền riêng tư và do nhà phát triển dẫn dắt cho các trải nghiệm kính AI. Mentra Live là chiếc kính thông minh đầu tiên của công ty, kết hợp camera 12MP với công nghệ AI.</p>
<p style="padding-left: 40px;">Có trụ sở tại San Francisco, đội ngũ Mentra gồm những cá nhân từng có kinh nghiệm làm việc tại Google, MIT, FitBit, Calvin Klein và Nike. Mentra được hậu thuẫn bởi các nhà đầu tư đẳng cấp thế giới, bao gồm Y Combinator, Amazon, Toyota Ventures, Hartmann Capital, Rich Miner và Jawed Karim.</p>
HTML,
        ],
        [
            'slug' => 'real-time-captions-with-mentraos',
            'title' => 'Phụ đề thời gian thực với MentraOS',
            'date' => '2026-02-06 19:51:22',
            'excerpt' => 'Mentra ra mắt ứng dụng Phụ đề thời gian thực mới: nhanh, chính xác, phân biệt được người nói và hoàn toàn miễn phí cho kính thông minh.',
            'image' => 'news/Caption_Blogs_6240a85f-4b0c-434f-949c-65c09cb099e4.png',
            'authors' => [['name' => 'Cayden Pierce', 'role' => 'CEO', 'avatar' => 'news/Cayden_Headshot.png']],
            'body' => <<<'HTML'
<p>Chúng tôi vừa ra mắt ứng dụng phụ đề dành cho kính thông minh với tốc độ nhanh, độ chính xác cao, hoạt động tốt trong môi trường ồn, có thể phân biệt người nói và hoàn toàn miễn phí.</p>
<p>Được xây dựng cho nhiều loại kính thông minh, bao gồm Even Realities và Vuzix, bất kỳ ai cũng có thể truy cập <a href="{{HOME_URL}}/phu-de/">Phụ đề</a> của Mentra thông qua MentraOS. Chỉ cần tải ứng dụng Mentra, ghép nối với kính thông minh của bạn, và chạm vào ứng dụng Phụ đề để xem trên điện thoại và trên kính.</p>
<p><img alt="" src="{{THEME_URI}}/assets/news/Screenshot_2026-02-05_at_1.44.58_PM.png"/>Còn được gọi là "phụ đề thời gian thực", mọi tính năng mới của chúng tôi đều đến từ cộng đồng người dùng, đặc biệt là cộng đồng người khiếm thính và khó nghe (HOH). (Nếu chưa, bạn nên <a href="https://discord.com/channels/973327320247042099/1340942811591217223">tham gia Discord của Mentra</a> để cùng đóng góp!) Trong quá trình thử nghiệm Captions 2.0, các thành viên khiếm thính/khó nghe của chúng tôi đã dành những đánh giá rất tích cực cho sản phẩm.</p>
<p><iframe height="315" src="https://www.youtube.com/embed/zrXNVFM8rww?si=TQkQMiOPPE24p-Tb" title="YouTube video player" width="560"></iframe><br/><br/><strong>Phân biệt người nói (Diarization)</strong></p>
<p>Một vấn đề lớn trước đây với các sản phẩm "phụ đề" cho kính thông minh là bạn không thể biết ai đang nói gì. <a href="{{HOME_URL}}/phu-de/">Mentra Captions</a> có thể phân biệt tới 15 người nói khác nhau! Điều tuyệt vời nhất là Phụ đề của chúng tôi hoạt động tốt ngay cả trong môi trường ồn ào và ngay cả khi bạn không nhìn trực tiếp vào người đang nói.</p>
<p><strong>Sao chép và dán văn bản</strong></p>
<p><img alt="" src="{{THEME_URI}}/assets/news/Caption_Blogs-1.png"/><br/><br/>Giờ đây bạn có thể sao chép mọi đoạn phụ đề! Một đề xuất chúng tôi nhận được từ người dùng là khả năng sao chép và dán văn bản từ Phụ đề. Ví dụ, nếu ai đó đang đọc số điện thoại của họ, người dùng muốn có thể sao chép đúng đoạn văn bản đó và dán vào bất cứ đâu. Tính năng này cũng rất hữu ích khi muốn tìm hiểu thêm, trích dẫn, hoặc lưu lại các đoạn văn bản.</p>
<p><strong>Nhiều ngôn ngữ cùng lúc</strong></p>
<p><a href="{{HOME_URL}}/phu-de/">Mentra Captions</a> có thể nhận diện đồng thời hơn 50 ngôn ngữ! Bạn không cần chuyển đổi qua lại giữa các ngôn ngữ trong một menu thả xuống, vì ứng dụng sẽ tự động nhận diện ngôn ngữ và phụ đề ngay. Người dùng của chúng tôi cho biết tính năng này rất hữu ích khi có nhiều người nói nhiều ngôn ngữ khác nhau trong cùng một phòng.<br/><br/><img alt="" src="{{THEME_URI}}/assets/news/Screenshot_2026-02-05_at_1.34.53_PM.png"/><br/>Mentra Captions 1.0 lần đầu ra mắt vào tháng 1 năm 2025 và nhanh chóng trở thành ứng dụng kính thông minh phổ biến nhất dành cho cộng đồng khiếm thính và khó nghe, nhưng cũng giúp ích cho những người gặp khó khăn khi nghe các giọng vùng miền khác nhau và xử lý thông tin. Nó cũng thay đổi cách cộng đồng HOH từ chỗ phải giơ điện thoại lên khắp nơi sang có thể hoàn toàn rảnh tay trong nhiều tình huống đời thực.</p>
<p>MentraOS trao cho kính thông minh khả năng truy cập nhiều ứng dụng tùy chỉnh, mà bạn có thể tìm thấy tại <a href="https://apps.mentraglass.com/">Mentra MiniApp Store</a>.</p>
<p>Chúng tôi rất vui được tiếp tục đồng hành cùng cộng đồng khiếm thính và khó nghe, giúp cuộc sống hằng ngày trở nên dễ dàng hơn!</p>
HTML,
        ],
    ];
}
