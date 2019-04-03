import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';
import { KioskuOrderPage } from './kiosku-order';

@NgModule({
  declarations: [
    KioskuOrderPage,
  ],
  imports: [
    IonicPageModule.forChild(KioskuOrderPage),
    TranslateModule.forChild()
  ],
})
export class KioskuOrderPageModule {}
