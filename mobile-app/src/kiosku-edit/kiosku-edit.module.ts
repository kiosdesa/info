import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';
import { KioskuEditPage } from './kiosku-edit';

@NgModule({
  declarations: [
    KioskuEditPage,
  ],
  imports: [
    IonicPageModule.forChild(KioskuEditPage),
    TranslateModule.forChild()
  ],
})
export class KioskuEditPageModule {}
