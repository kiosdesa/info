import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { TranslateModule } from '@ngx-translate/core';
import { AnnouncePage } from './announce';

@NgModule({
  declarations: [
    AnnouncePage,
  ],
  imports: [
    IonicPageModule.forChild(AnnouncePage),
    TranslateModule.forChild()
  ],
})
export class AnnouncePageModule {}
