import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';
import { KioskuSellingPage } from './kiosku-selling';

@NgModule({
  declarations: [
    KioskuSellingPage,
  ],
  imports: [
    IonicPageModule.forChild(KioskuSellingPage),
    TranslateModule.forChild()
  ],
})
export class KioskuSellingPageModule {}
