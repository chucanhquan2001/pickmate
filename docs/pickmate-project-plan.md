# PickMate / PickHub — Project Plan

## 1. Tổng quan dự án

Web app quản lý hoạt động Pickleball cho CLB, nhóm chơi hoặc giải nội bộ.

CLB là nơi chứa kho thành viên, sân và nhiều minigame. Mỗi minigame tự chọn người từ kho chung, tự chọn quy chế, tự chứa trận đấu và chỉ có bảng xếp hạng của chính nó. Không có bảng điểm tổng của CLB.

Mục tiêu chính:

- Quản lý thành viên dùng chung cho mọi minigame.
- Tạo minigame, chọn roster và chọn quy chế.
- Tạo và quản lý lịch đấu trong một minigame.
- Nhập kết quả trận đấu.
- Tự động cập nhật BXH của đúng minigame đó.
- Quản lý buổi chơi và sân.
- Hỗ trợ chia đội nhanh, dễ dùng trên mobile.
- UI/UX đơn giản, người không rành công nghệ vẫn thao tác được.

Định hướng triển khai:

- Làm MVP gọn trước.
- Mobile-first vì phần lớn thao tác sẽ diễn ra ngay tại sân.
- Một app Laravel, giao diện Inertia và Vue trong cùng repo.
- Có thể mở rộng thành SaaS quản lý nhiều CLB sau này.

---

## 2. Tên dự án

### Tên đề xuất

#### PickMate
Ý nghĩa: người bạn đồng hành khi chơi Pickleball.

Tagline:

> Play. Track. Rank.

Phù hợp nếu:
- Làm sản phẩm nội bộ.
- Làm portfolio.
- Muốn thương hiệu trẻ, thân thiện.

#### PickHub
Ý nghĩa: trung tâm quản lý toàn bộ hoạt động Pickleball.

Tagline:

> Your Pickleball Club, All in One Place.

Phù hợp nếu:
- Muốn phát triển SaaS.
- Một hệ thống quản lý nhiều CLB.
- Có định hướng mở rộng cộng đồng sau này.

### Tên khác

- PickMatch
- PickRank
- PickClub
- PickPlay
- PickBoard
- PickZone
- PickUp
- Pickly

### Khuyến nghị

Tên dự án code ban đầu:

`pickmate`

Nếu định hướng SaaS nhiều CLB:

`pickhub`

---

# 3. Đối tượng sử dụng

## Owner

Người sở hữu CLB / hệ thống.

Quyền:

- Quản lý toàn bộ hệ thống.
- Quản lý admin.
- Quản lý thành viên.
- Tạo và đóng minigame.
- Chọn roster và quy chế của từng minigame.
- Quản lý lịch đấu trong minigame.
- Quản lý kết quả.
- Quản lý sân.
- Quản lý buổi chơi.
- Cấu hình CLB.

## Admin

Người vận hành CLB.

Quyền:

- Quản lý thành viên.
- Tạo và đóng minigame.
- Chọn roster và quy chế của từng minigame.
- Tạo lịch đấu trong minigame.
- Nhập kết quả.
- Quản lý buổi chơi.
- Quản lý sân.

## Member

Người chơi.

Quyền:

- Xem danh sách minigame.
- Xem lịch đấu của minigame.
- Xem kết quả.
- Xem BXH của từng minigame.
- Xem profile.
- Xem thống kê cá nhân theo từng minigame.

MVP chưa cần bắt buộc tất cả member phải có tài khoản login.

Admin có thể tạo member trước.

---

# 4. Module chính

## 4.1 Dashboard

Dashboard là màn hình tổng quan và nên là trang được sử dụng nhiều nhất.

Khi chưa chọn minigame, dashboard liệt kê các minigame đang chạy:

- Tên minigame.
- Thể thức.
- Trạng thái.
- Số người trong roster.
- Số trận.
- Nút tạo minigame.
- Nút thêm thành viên.

Khi đã chọn một minigame, dashboard của minigame đó hiển thị:

- Tên minigame đang chọn.
- Số người trong roster.
- Số trận tuần này của minigame.
- Tổng trận đã đấu của minigame.
- Trận hôm nay.
- Trận sắp diễn ra.
- Top BXH của minigame đó.
- Kết quả gần nhất của minigame đó.
- Nút tạo trận nhanh.

Không hiển thị top BXH toàn CLB.

Ví dụ khi đã chọn minigame:

```text
Minigame: Đôi nam tháng 9
Đang chạy · Đôi nam

12 người trong roster
6 Trận tuần này
28 Trận đã đấu

BXH minigame
1. Nguyễn Văn A      48 điểm
2. Trần Văn B        41 điểm
3. Lê Văn C          36 điểm

Hôm nay

19:00 · Sân 01

Nguyễn Văn A / Trần Văn B
              VS
Lê Văn C / Phạm Văn D

[Sửa lịch] [Nhập kết quả]
```

---

## 4.2 Thành viên

### Danh sách thành viên

Kho thành viên thuộc CLB. Minigame không tạo thành viên riêng; admin chọn người từ danh sách này.

Hiển thị:

- Avatar.
- Họ tên.
- Nickname.
- Số minigame đã tham gia.
- Trạng thái.

Có:

- Search.
- Filter.
- Sort theo tên.
- Thêm thành viên.
- Sửa thông tin.
- Active / Inactive.

Không có hạng hay điểm tổng của CLB trên danh sách này. Hạng và điểm chỉ tồn tại trong từng minigame.

### Thông tin member

Các field cơ bản:

```text
Avatar
Họ tên
Nickname
Giới tính
Ngày sinh
Số điện thoại
Email
Trình độ
Ngày tham gia CLB
Trạng thái
```

Trình độ:

```text
Beginner
Intermediate
Advanced
```

Có thể mở rộng sau:

```text
Beginner
2.0
2.5
3.0
3.5
4.0
4.5+
```

### Profile thành viên

Hiển thị theo từng minigame đã tham gia:

- Tên minigame.
- Hạng trong minigame đó.
- Điểm trong minigame đó.
- Tổng trận, thắng, thua, win rate của minigame đó.

Và phần lịch sử:

- Lịch sử đấu, mỗi trận ghi rõ thuộc minigame nào.
- Partner chơi nhiều nhất.
- Đối thủ gặp nhiều nhất.

Đây là thống kê cá nhân theo minigame, không phải một BXH tổng của CLB.

---

## 4.3 Minigame

Minigame là cấp bắt buộc của MVP. Mọi trận, kết quả và BXH đều nằm trong một minigame. Phải tạo minigame, chọn người và chọn quy chế trước khi tạo trận.

### Tạo minigame

Thông tin:

```text
Tên
Mô tả
Thời gian bắt đầu
Thời gian kết thúc
Thể thức
Trạng thái
```

Thể thức, chọn một:

```text
Đơn nam
Đơn nữ
Đôi nam
Đôi nữ
Đôi nam nữ
```

Một minigame chỉ có một thể thức và một BXH. Muốn hai bảng, ví dụ vừa đơn nam vừa đôi nam, thì tạo hai minigame.

Trạng thái:

```text
Nháp
Đang chạy
Đã đóng
```

Nháp: đang chọn người và quy chế, chưa nhận trận mới.

Đang chạy: được tạo trận và nhập kết quả. Kết quả cập nhật BXH của minigame này.

Đã đóng: khóa BXH, không tạo trận mới, không sửa kết quả theo workflow thường.

### Chọn roster

Chọn thành viên bằng search từ kho thành viên chung của CLB.

```text
Tìm thành viên

☑ Nguyễn Văn A
☑ Trần Văn B
☐ Lê Văn C
```

Chỉ thành viên Active mới được chọn. Một thành viên có thể ở nhiều minigame cùng lúc. Điểm không cộng dồn sang minigame khác.

### Chọn quy chế

Quy chế được lưu trên chính minigame tại thời điểm tạo. Sửa minigame khác, hoặc sửa bộ mặc định của CLB sau này, không đổi quy chế của minigame đang chạy.

MVP gồm:

```text
Điểm số mặc định    11 / 15 / 21
Best of             1 / 3 / 5
Điểm tham gia
Điểm thắng
Điểm thua
Thưởng thắng sạch
```

Cách tính điểm mặc định gợi ý:

```text
Tham gia    +1
Thắng       +3
Thắng sạch  +1
```

Elo gắn vào quy chế minigame ở version sau, không đặt ở Settings CLB.

### Màn chi tiết

- Tên, thể thức, trạng thái, thời gian.
- Roster.
- Quy chế đang áp dụng.
- Lối tắt sang lịch đấu, nhập kết quả và BXH của minigame này.
- Đóng hoặc mở lại minigame.

---

# 5. Lịch đấu

Mọi trận thuộc một minigame đang chạy. Không tạo trận ở cấp CLB.

Người chơi chỉ được chọn từ roster của minigame đó. Thể thức lấy từ quy chế minigame, không chọn lại trên từng trận.

Minigame nháp hoặc đã đóng thì không tạo trận mới.

## Tạo trận

Thông tin:

```text
Minigame đang chọn
Ngày
Giờ
Sân
Đội 1
Đội 2
```

Ví dụ:

```text
Minigame: Đôi nam tháng 9
Thể thức: Đôi nam

Ngày: 30/09/2026
Giờ: 19:00
Sân: Court 01

Đội 1:
Nguyễn Văn A
Trần Văn B

Đội 2:
Lê Văn C
Phạm Văn D

[Tạo trận]
```

## Trạng thái trận

Backend:

```text
scheduled
playing
completed
cancelled
```

UI:

```text
Sắp diễn ra
Đang đấu
Hoàn thành
Đã hủy
```

---

# 6. Nhập kết quả

Mục tiêu:

- Nhập nhanh.
- Ít thao tác.
- Dùng tốt trên điện thoại.

Ví dụ:

```text
Nguyễn Văn A / Trần Văn B
              VS
Lê Văn C / Phạm Văn D

Set 1
11 - 7

Set 2
11 - 9

[+ Thêm set]

Winner:
Nguyễn Văn A / Trần Văn B

[Xác nhận kết quả]
```

Luồng xử lý:

```text
Match trong minigame
  ↓
Result
  ↓
Update statistics của minigame
  ↓
Calculate ranking theo quy chế minigame
  ↓
Update BXH của đúng minigame đó
```

Kết quả không cộng điểm sang minigame khác và không tạo hạng CLB.

Không cho admin sửa BXH trực tiếp trong workflow thông thường.

BXH của minigame được tính lại từ kết quả trận thuộc minigame đó.

Minigame đã đóng thì không nhập hoặc sửa kết quả theo workflow thường.

---

# 7. Bảng xếp hạng

Mỗi minigame có một BXH riêng. Không có BXH tổng của CLB.

Thể thức đã nằm trong quy chế minigame, nên BXH không lọc đơn/đôi trên cùng một bảng. Muốn bảng đơn nam và bảng đôi nam thì đó là hai minigame.

Chỉ thành viên trong roster mới có mặt trên BXH của minigame.

## Cột hiển thị

```text
Rank
Member
Matches
Wins
Losses
Win Rate
Points
```

Ví dụ:

```text
#   Thành viên          Trận   Thắng   Thua   Điểm

1   Nguyễn Văn A         20      16      4     128
2   Trần Văn B           18      13      5     112
3   Lê Văn C             21      12      9     103
```

Có thể thêm trong cùng minigame:

- Rank tăng / giảm.
- Form 5 trận gần nhất.
- Filter theo tháng.

---

# 8. Cách tính điểm

Cách tính điểm thuộc quy chế của từng minigame, không phải một cấu hình dùng chung cho cả CLB.

Quy chế được copy và lưu trên minigame lúc tạo. Đổi bộ mặc định của CLB, hoặc đổi quy chế minigame khác, không đổi điểm của minigame đang chạy.

## MVP

Nên dùng cách tính đơn giản.

Phương án 1:

```text
Tham gia    +1
Thắng       +3
Thắng 2-0   +1 bonus
```

Phương án 2:

```text
Thắng       +10
Thua        +3
```

Khuyến nghị MVP, lưu trên quy chế minigame:

```text
Participation: +1
Win:           +3
Clean win:     +1
```

Sau này có thể thêm vào quy chế minigame:

```text
Point
Elo
DUPR-style
```

Elo không đặt ở Settings CLB.

---

# 9. Random chia đội

Đây nên là feature ưu tiên sau MVP, dùng bên trong một minigame.

Người chơi được chọn từ roster của minigame đó. Trận sinh ra thuộc minigame và cộng điểm theo quy chế của minigame.

Luồng:

```text
Chọn người chơi

☑ An
☑ Bình
☑ Cường
☑ Dũng
☑ Hùng
☑ Linh
☑ Minh
☑ Nam

[Chia đội]
```

Kết quả:

```text
Court 1

An + Bình
vs
Cường + Dũng

Court 2

Hùng + Linh
vs
Minh + Nam
```

Chế độ:

```text
Random
Cân bằng trình độ
Hạn chế ghép lại partner gần nhất
Hạn chế gặp lại đối thủ gần nhất
```

Mở rộng:

- Ưu tiên cân bằng theo rank.
- Không cho cùng partner liên tục.
- Tự tạo nhiều round.

---

# 10. Buổi chơi

Buổi chơi thuộc Version 1.1. Một buổi chơi luôn thuộc một minigame, không đứng ngang hàng với minigame.

Buổi chơi giúp tạo nhiều trận nhanh trong tối đó. Các trận sinh ra thuộc minigame cha và cập nhật BXH của minigame đó.

Người tham gia chỉ chọn từ roster của minigame. Sân lấy từ danh sách sân chung của CLB.

Thông tin:

```text
Minigame
Ngày
Thời gian bắt đầu
Thời gian kết thúc
Danh sách sân
Danh sách người tham gia
```

Ví dụ:

```text
Buổi chơi
Minigame: Đôi nam tháng 9

30/09/2026

18:00 → 21:00

Courts:
☑ Court 1
☑ Court 2
☑ Court 3

12 players trong roster

[Tạo lịch tự động]
```

Hệ thống tạo:

```text
Round 1

Court 1
A + B vs C + D

Court 2
E + F vs G + H

Court 3
I + J vs K + L
```

Round tiếp theo tự đổi partner / opponent.

---

# 11. Quản lý sân

Thông tin sân:

```text
Tên sân
Mã sân
Trạng thái
Ghi chú
```

Ví dụ:

```text
Court 1
Court 2
Court 3
Court 4
```

Schedule view có thể hiển thị:

```text
             Court 1       Court 2       Court 3

18:00        A/B-C/D          -              -

19:00        E/F-G/H       A/C-B/D           -

20:00           -          E/G-F/H        A/D-B/C
```

---

# 12. Statistics

Thống kê trận, điểm, hạng và head-to-head tính trong phạm vi một minigame.

## Member statistics

Trong một minigame:

```text
Total Matches
Wins
Losses
Win Rate
Current Streak
Longest Win Streak
Points
Current Rank
```

Có thể thêm:

```text
Favorite Partner
Most Played Opponent
Best Partner
Strongest Opponent
```

## Head to Head

So trong cùng một minigame.

Ví dụ:

```text
Minigame: Đôi nam tháng 9

Nguyễn Văn A
      VS
Nguyễn Văn B

12 trận

A thắng: 7
B thắng: 5

58% vs 42%
```

---

# 13. Navigation

## Desktop

```text
LOGO

Dashboard

Minigame
├── Danh sách
├── Roster
└── Quy chế

Thi đấu
├── Lịch đấu
├── Kết quả
└── Tạo trận

BXH

Thành viên

Buổi chơi

Sân

Thống kê

────────────

Cài đặt
```

Lịch đấu, kết quả và BXH luôn thuộc minigame đang chọn. Đổi minigame thì các màn này đổi theo. Nhờ ngữ cảnh đó, tạo trận trên mobile vẫn nhanh, không phải chọn minigame lại mỗi lần.

## Mobile

Bottom navigation:

```text
Home
Minigame
Create
Ranking
Members
```

Home là dashboard của minigame đang chọn. Minigame mở danh sách để chuyển hoặc tạo mới. Ranking là BXH của minigame đang chọn. Lịch đấu nằm trong Home và trong chi tiết minigame.

Nút Create nên nằm giữa và nổi bật. Create tạo trận trong minigame đang chạy.

---

# 14. UI / UX principles

## Mobile-first

Các thao tác quan trọng phải dễ dùng bằng một tay:

- Tạo trận.
- Chọn người.
- Nhập điểm.
- Xem BXH.
- Xem lịch.

## Hạn chế modal sâu

Không nên:

```text
Modal
→ Modal
→ Modal
```

Ưu tiên:

```text
Page
→ Drawer / Bottom Sheet
→ Confirm
```

## Thao tác nhanh

Ví dụ nhập kết quả:

```text
[-] 11 [+]     [-] 7 [+]
```

Hoặc input lớn:

```text
11     :     7
```

## Status rõ ràng

Sử dụng badge:

```text
Sắp diễn ra
Đang đấu
Hoàn thành
Đã hủy
```

## Search-first

Member picker nên có search ngay khi mở.

Không dùng select HTML dài hàng trăm thành viên.

---

# 15. Settings

## Club

```text
Club Name
Logo
Timezone
Language
```

## Bộ mặc định khi tạo minigame

CLB chỉ giữ bộ mặc định. Lúc tạo minigame, hệ thống copy bộ này vào quy chế của minigame. Sửa bộ mặc định sau đó không đổi minigame đã tạo.

```text
Default Score

11
15
21

Best of

1
3
5

Participation
Win
Loss
Clean Win Bonus
```

Quy chế thật sự dùng để tính BXH nằm trên minigame, mô tả ở mục 4.3 và mục 8.

---

# 16. Tech Stack

Một app Laravel. Giao diện Inertia + Vue nằm trong cùng repo. Stack, đăng nhập Google / Facebook và cách tổ chức code nằm ở [tech-stack.md](tech-stack.md).

Đăng nhập bằng Google hoặc Facebook, không dùng mật khẩu.

---

# 17. Database Design

## clubs

```text
id
name
slug
logo
timezone
language
status
default_score
default_best_of
default_participation_points
default_win_points
default_loss_points
default_clean_win_bonus
created_at
updated_at
```

Các field `default_*` chỉ là bộ mặc định để copy khi tạo minigame. BXH không đọc các field này.

## minigames

```text
id
club_id
name
description
format
status
starts_at
ends_at
default_score
best_of
participation_points
win_points
loss_points
clean_win_bonus
created_by
created_at
updated_at
```

format:

```text
single_male
single_female
double_male
double_female
double_mixed
```

status:

```text
draft
active
closed
```

Quy chế điểm và thể thức lưu trên dòng minigame này.

## minigame_members

Roster. Thành viên vẫn nằm ở `members`; bảng này chỉ ghi ai được chọn vào minigame.

```text
id
minigame_id
member_id
created_at
```

## users

Đăng nhập bằng Google hoặc Facebook, không dùng mật khẩu. Chi tiết ở [tech-stack.md](tech-stack.md).

```text
id
club_id nullable
name
email nullable
avatar nullable
role
status
created_at
updated_at
```

## social_accounts

```text
id
user_id
provider
provider_user_id
email nullable
created_at
updated_at
```

provider:

```text
google
facebook
```

Role:

```text
owner
admin
member
```

## members

```text
id
club_id
user_id nullable
name
nickname
avatar
gender
birthday
phone
email
level
joined_at
status
created_at
updated_at
```

## courts

```text
id
club_id
name
code
status
note
created_at
updated_at
```

## sessions

Buổi chơi, thuộc một minigame. Dùng từ Version 1.1.

```text
id
minigame_id
name
play_date
start_time
end_time
status
created_by
created_at
updated_at
```

## session_members

```text
id
session_id
member_id
status
created_at
```

## session_courts

```text
id
session_id
court_id
```

## matches

```text
id
minigame_id
session_id nullable
court_id nullable

status

scheduled_at
started_at
completed_at

created_by
created_at
updated_at
```

Thể thức lấy từ `minigames.format`, không lưu riêng trên trận.

## match_players

```text
id
match_id
member_id
team
position
created_at
```

team:

```text
1
2
```

## match_sets

```text
id
match_id
set_number
team_1_score
team_2_score
created_at
updated_at
```

## rankings

Một dòng là hạng của một thành viên trong một minigame. Không có `club_id` và không có `ranking_type`, vì không có BXH CLB và mỗi minigame chỉ có một bảng.

```text
id
minigame_id
member_id
matches
wins
losses
points
rating
rank
updated_at
```

## ranking_logs

Lưu lịch sử thay đổi điểm trong một minigame.

```text
id
minigame_id
member_id
match_id
old_point
change_point
new_point
old_rank
new_rank
created_at
```

---

# 18. Business Flow

## Minigame flow

```text
Create Minigame
      ↓
Pick Members từ kho chung
      ↓
Choose Rules
      ↓
Active
      ↓
Create Match
      ↓
Enter Result
      ↓
Update BXH của minigame
      ↓
Close Minigame
      ↓
Khóa BXH
```

## Match flow

```text
Minigame đang chạy
     ↓
Create Match
     ↓
Scheduled
     ↓
Playing
     ↓
Enter Result
     ↓
Completed
     ↓
Calculate Stats
     ↓
Update Ranking của minigame
```

## Session flow

Buổi chơi nằm trong minigame.

```text
Select Minigame
      ↓
Create Session
      ↓
Select Members từ roster
      ↓
Select Courts
      ↓
Generate Teams
      ↓
Generate Matches trong minigame
      ↓
Play
      ↓
Enter Results
      ↓
Update BXH của minigame
```

---

# 19. MVP Scope

## Version 1.0

Chỉ tập trung các module sau. Mọi trận, kết quả và BXH nằm trong một minigame.

### 1. Dashboard

- Danh sách minigame đang chạy.
- Dashboard của minigame đang chọn.
- Match today của minigame đó.
- Top BXH của minigame đó.
- Recent results của minigame đó.

### 2. Members

- CRUD kho thành viên chung.
- Search.
- Member profile theo từng minigame, không có hạng CLB.

### 3. Minigames

- Tạo, sửa, đóng.
- Chọn roster từ kho thành viên.
- Chọn và lưu quy chế trên minigame.

### 4. Matches

- Create trong minigame đang chạy.
- Update.
- Cancel.
- Match detail.
- Người chơi lấy từ roster.

### 5. Results

- Enter scores.
- Calculate winner.
- Cộng điểm theo quy chế của minigame.

### 6. Ranking

- Auto update BXH của đúng minigame.
- Không có BXH tổng CLB.
- Member ranking trong minigame.

### 7. Settings

- Club settings.
- Bộ mặc định để copy khi tạo minigame mới.

---

# 20. Version 1.1

Thêm:

```text
Sessions
Courts
Random Team
Auto Schedule
```

Buổi chơi, chia đội và lịch tự động đều thuộc một minigame. Người chơi lấy từ roster. Trận sinh ra cập nhật BXH của minigame đó.

Mục tiêu:

Giúp admin, trong một minigame đang chạy, tạo một buổi chơi hoàn chỉnh chỉ trong vài thao tác.

---

# 21. Version 1.2

Thêm:

```text
Head to Head
Advanced Statistics
Elo Ranking
QR Check-in
Member Login
Notifications
```

Elo là một cách tính trong quy chế minigame, không phải bảng điểm của CLB. Head to head và thống kê nâng cao tính trong phạm vi minigame.

---

# 22. Version 2.0

Nếu phát triển SaaS:

```text
Multi Club
Subscription
Public Club Page
Public Ranking
Tournament
Team Tournament
Season
League
Invitation Link
Payment
Push Notification
```

Tournament, Season và League là mở rộng sau. Chúng không thay cấp minigame. Public ranking, nếu làm, là BXH công khai của từng minigame.

---

# 23. Folder Structure Laravel

Gợi ý:

```text
app/

├── Models/
│   ├── Club.php
│   ├── Member.php
│   ├── Minigame.php
│   ├── MinigameMember.php
│   ├── Court.php
│   ├── PlaySession.php
│   ├── MatchGame.php
│   ├── MatchPlayer.php
│   ├── MatchSet.php
│   └── Ranking.php
│
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php
│   │   ├── MemberController.php
│   │   ├── MinigameController.php
│   │   ├── MatchController.php
│   │   ├── ResultController.php
│   │   ├── RankingController.php
│   │   ├── SessionController.php
│   │   └── CourtController.php
│
├── Services/
│   ├── MinigameService.php
│   ├── MatchService.php
│   ├── RankingService.php
│   ├── TeamGeneratorService.php
│   └── ScheduleGeneratorService.php
```

---

# 24. Routes

Ví dụ:

```text
/dashboard

/members
/members/create
/members/{id}

/minigames
/minigames/create
/minigames/{id}
/minigames/{id}/roster
/minigames/{id}/rules

/minigames/{id}/matches
/minigames/{id}/matches/create
/minigames/{id}/matches/{match}
/minigames/{id}/matches/{match}/result

/minigames/{id}/rankings

/minigames/{id}/sessions
/minigames/{id}/sessions/create
/minigames/{id}/sessions/{session}

/courts

/minigames/{id}/statistics

/settings
```

Lịch đấu, kết quả và BXH không có route cấp CLB. Chúng luôn nằm dưới minigame đang chọn.

---

# 25. Services nên tách riêng

## MinigameService

```text
createMinigame()
updateRules()
syncRoster()
activateMinigame()
closeMinigame()
```

`updateRules()` chỉ áp dụng cho minigame đó. Đóng minigame thì khóa BXH và chặn trận mới.

## RankingService

Chịu trách nhiệm tính BXH của một minigame. Mọi hàm nhận `minigame`, dùng quy chế lưu trên minigame đó.

```text
calculatePoints(minigame, match)
updateMemberRanking(minigame, member)
rebuildRanking(minigame)
calculateElo(minigame, match)
```

## MatchService

Trận luôn thuộc một minigame đang chạy. Người chơi phải nằm trong roster.

```text
createMatch(minigame)
startMatch()
completeMatch()
cancelMatch()
calculateWinner()
```

## TeamGeneratorService

Chia đội trong roster của một minigame. Trận sinh ra thuộc minigame đó.

```text
randomTeams(minigame)
balancedTeams(minigame)
avoidRecentPartners()
avoidRecentOpponents()
```

## ScheduleGeneratorService

Xếp lịch cho một buổi chơi thuộc minigame.

```text
generateRounds(minigame)
assignCourts()
assignTimeSlots()
```

Không nên nhét toàn bộ logic vào Controller.

---

# 26. Priorities

## P0 — Must Have

```text
Members
Minigames
Matches
Results
Ranking
Dashboard
Settings
```

## P1 — Important

```text
Courts
Sessions
Random Team
Auto Schedule
Statistics
```

## P2 — Nice to Have

```text
Elo
QR Check-in
Notifications
Head to Head
Member Account
```

## P3 — Future SaaS

```text
Multi Club
Subscriptions
Tournament
Public Ranking
Payments
```

Tournament và Public Ranking mở rộng minigame, không thay cấp này và không tạo lại BXH tổng của CLB.

---

# 27. Development Order

Khuyến nghị code theo thứ tự:

```text
1. Authentication
2. Club
3. Members
4. Minigame
5. Roster và quy chế
6. Matches
7. Match Players
8. Match Sets
9. Result Logic
10. Ranking Service
11. Dashboard
12. Settings
13. Courts
14. Sessions
15. Team Generator
16. Auto Schedule
17. Statistics
```

Lý do:

```text
Members
   ↓
Minigame + roster + quy chế
   ↓
Matches
   ↓
Results
   ↓
BXH của minigame
```

là dependency chính của toàn hệ thống.

---

# 28. MVP Definition of Done

MVP được coi là hoàn thành khi admin có thể:

1. Login.
2. Tạo thành viên trong kho chung.
3. Tạo minigame.
4. Chọn người từ kho thành viên.
5. Chọn quy chế và lưu trên minigame.
6. Tạo trận trong minigame đang chạy.
7. Chọn người chơi từ roster.
8. Chọn sân.
9. Nhập kết quả.
10. Hệ thống xác định winner.
11. BXH của đúng minigame tự cập nhật.
12. Không có BXH tổng của CLB.
13. Member xem được lịch sử đấu trong minigame.
14. Dashboard hiển thị minigame đang chạy và dữ liệu của minigame đang chọn.

Luồng phải hoàn thành tốt trên mobile. Minigame đang chọn được giữ để tạo trận vẫn dưới 30 giây.

---

# 29. Core Product Loop

Luồng sử dụng chính:

```text
Create Member
      ↓
Create Minigame
      ↓
Pick Members
      ↓
Choose Rules
      ↓
Create Match
      ↓
Play
      ↓
Enter Result
      ↓
Update BXH của minigame
      ↓
View BXH của minigame
      ↓
Create Next Match
```

Mục tiêu UX:

> Trong minigame đang chọn, một trận đấu mới có thể được tạo trong dưới 30 giây.

> Một kết quả có thể được nhập trong dưới 15 giây.

---

# 30. Product Direction

Trục sản phẩm là tổ chức trọn một minigame: chọn người từ kho chung, chọn quy chế, đấu, rồi cập nhật BXH riêng. Không còn một BXH cho cả CLB.

Buổi chơi, chia đội và xếp lịch là phần làm nhanh một tối bên trong minigame đó:

```text
Minigame
+
Buổi chơi
+
Random / Balanced Team
+
Auto Schedule
+
BXH của minigame
```

Thay vì chỉ là web CRUD quản lý trận đấu, sản phẩm có thể phát triển thành:

> Công cụ tổ chức một minigame Pickleball hoàn chỉnh, từ lúc chọn người và quy chế đến lúc cập nhật BXH riêng.

Core value:

```text
Which minigame?
     ↓
Who is on the roster?
     ↓
Which rules?
     ↓
Who plays?
     ↓
Who partners with whom?
     ↓
Which court?
     ↓
What time?
     ↓
Who won?
     ↓
How does this minigame ranking change?
```

Nếu làm tốt flow này, app sẽ có giá trị thực tế cao hơn nhiều so với chỉ quản lý danh sách và kết quả.
